<?php

namespace App\Actions\AI;

use App\Actions\Profile\RefreshProfessionalProfileAction;
use App\Models\CVParsedData;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\Files\PrivateFileStorageService;
use App\Services\Profile\SkillNormalizer;
use Illuminate\Support\Facades\Storage;

class ParseCVAction
{
    public function __construct(
        private readonly AIService $aiService,
        private readonly PrivateFileStorageService $files,
        private readonly SkillNormalizer $skillNormalizer,
        private readonly RefreshProfessionalProfileAction $refreshProfile,
    ) {}

    /**
     * استخراج البيانات من ملف CV.
     *
     * @param User         $user        المستخدم صاحب الـ CV
     * @param string       $cvPath      مسار الـ CV في Storage
     * @param bool         $updateProfile هل نعبّئ الملف الشخصي تلقائياً؟
     * @param int|null     $applicationId الطلب المرتبط (اختياري)
     *
     * @throws \RuntimeException إذا تعذّر قراءة الـ CV
     */
    public function execute(
        User $user,
        string $cvPath,
        bool $updateProfile = false,
        ?int $applicationId = null
    ): CVParsedData {
        // ── استخراج النص من PDF ──────────────────────────────────
        $text = $this->extractText($cvPath);

        if (empty(trim($text))) {
            throw new \RuntimeException('لم نتمكن من قراءة ملف الـ CV. تأكد أنه PDF قابل للقراءة.');
        }

        // ── بناء الـ Prompt ──────────────────────────────────────
        $prompt = $this->buildPrompt($text);

        // ── إرسال لـ AI ──────────────────────────────────────────
        $rawResponse = $this->aiService->execute(
            feature:   'cv_parser',
            prompt:    $prompt,
            options:   ['temperature' => 0.1], // دقة عالية
            userId:    $user->id,
        );

        // ── تحليل الـ JSON ───────────────────────────────────────
        $parsedData = $this->parseResponse($rawResponse);

        // ── حفظ النتيجة ──────────────────────────────────────────
        $cvParsed = CVParsedData::create([
            'user_id'          => $user->id,
            'application_id'   => $applicationId,
            'cv_path'          => $cvPath,
            'parsed_data'      => $parsedData,
            'confidence_score' => $parsedData['confidence'] ?? 0.8,
            'profile_updated'  => false,
            'parsed_at'        => now(),
        ]);

        // ── تعبئة الملف الشخصي (اختياري) ────────────────────────
        if ($updateProfile) {
            $this->updateUserProfile($user, $parsedData);
            $cvParsed->update(['profile_updated' => true]);
        }

        return $cvParsed;
    }

    // ========================================================
    // Private Helpers
    // ========================================================

    /**
     * استخراج النص من PDF.
     *
     * يحاول pdftotext أولاً (دقيق).
     * Fallback: يقرأ الملف كـ binary (أقل دقة).
     */
    private function extractText(string $cvPath): string
    {
        $disk = $this->files->disk();
        $fullPath = Storage::disk($disk)->path($cvPath);

        // محاولة pdftotext (متوفر في معظم servers)
        if (file_exists($fullPath)) {
            $output = shell_exec("pdftotext " . escapeshellarg($fullPath) . " -");
            if ($output && strlen($output) > 50) {
                return $output;
            }
        }

        // Fallback: قراءة مباشرة (للـ PDFs البسيطة)
        $content = Storage::disk($disk)->get($cvPath);
        // استخراج النص بشكل بدائي من PDF binary
        preg_match_all('/\(([^)]+)\)/', $content, $matches);
        return implode(' ', $matches[1] ?? []);
    }

    /**
     * بناء الـ Prompt لاستخراج بيانات CV.
     */
    private function buildPrompt(string $cvText): string
    {
        // نحدد حجم النص لتجنب تجاوز token limit
        $truncatedText = mb_substr($cvText, 0, 4000);

        return <<<PROMPT
أنت محلل سير ذاتية محترف. استخرج البيانات من هذا الـ CV وأرجع JSON فقط بدون أي نص إضافي.

الـ JSON يجب أن يحتوي هذه الحقول:
{
  "name": "الاسم الكامل",
  "email": "البريد الإلكتروني",
  "phone": "رقم الهاتف",
  "summary": "ملخص احترافي مختصر (3-4 جمل)",
  "skills": ["مهارة 1", "مهارة 2", "..."],
  "experience": ["وصف تجربة 1", "وصف تجربة 2", "..."],
  "education": ["وصف تعليم 1", "وصف تعليم 2", "..."],
  "languages": ["عربي", "إنجليزي", "..."],
  "years_experience": 3,
  "confidence": 0.9
}

قواعد مهمة:
- أرجع JSON فقط بدون markdown أو backticks
- إذا لم تجد بيانات لحقل ما أرجع null أو []
- skills يجب أن تكون مهارات تقنية أو مهنية واضحة
- confidence من 0 إلى 1 (مدى جودة الاستخراج)

نص الـ CV:
{$truncatedText}
PROMPT;
    }

    /**
     * تحليل الـ JSON المُرجَع من AI.
     */
    private function parseResponse(string $response): array
    {
        // تنظيف الـ response من markdown إذا وُجد
        $clean = preg_replace('/```json\n?|\n?```/', '', $response);
        $clean = trim($clean);

        $data = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // محاولة استخراج JSON من داخل النص
            preg_match('/\{.*\}/s', $clean, $matches);
            if (! empty($matches[0])) {
                $data = json_decode($matches[0], true);
            }
        }

        if (! is_array($data)) {
            return [
                'skills'     => [],
                'experience' => [],
                'education'  => [],
                'languages'  => [],
                'confidence' => 0.3,
            ];
        }

        return $data;
    }

    /**
     * تعبئة الملف الشخصي تلقائياً من البيانات المستخرجة.
     */
    private function updateUserProfile(User $user, array $data): void
    {
        $updates = [];

        if (! empty($data['summary']) && empty($user->bio)) {
            $updates['bio'] = $data['summary'];
        }

        if (! empty($data['phone']) && empty($user->phone)) {
            $updates['phone'] = $data['phone'];
        }

        if (! empty($updates)) {
            $user->update($updates);
        }

        foreach ($data['skills'] ?? [] as $index => $skillName) {
            $skill = $this->skillNormalizer->findOrCreate((string) $skillName, ['source' => 'cv_parser']);
            $user->canonicalSkills()->syncWithoutDetaching([
                $skill->id => [
                    'source' => 'cv_parser',
                    'confidence_score' => $data['confidence'] ?? null,
                    'sort_order' => $index + 1,
                ],
            ]);
        }

        if ($user->experiences()->doesntExist()) {
            foreach ($data['experience'] ?? [] as $index => $summary) {
                $user->experiences()->create([
                    'title' => 'Imported CV Experience',
                    'summary' => (string) $summary,
                    'source' => 'cv_parser',
                    'confidence_score' => $data['confidence'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        if ($user->educations()->doesntExist()) {
            foreach ($data['education'] ?? [] as $index => $description) {
                $user->educations()->create([
                    'institution_name' => 'Imported from CV',
                    'description' => (string) $description,
                    'source' => 'cv_parser',
                    'confidence_score' => $data['confidence'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        $this->refreshProfile->execute($user->fresh());
    }
}
