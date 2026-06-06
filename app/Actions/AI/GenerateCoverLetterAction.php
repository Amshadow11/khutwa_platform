<?php

namespace App\Actions\AI;

use App\Models\Job;
use App\Models\User;
use App\Services\AI\AIService;

class GenerateCoverLetterAction
{
    public function __construct(
        private readonly AIService $aiService,
    ) {}

    /**
     * توليد رسالة تغطية احترافية.
     *
     * Rate Limiting:
     *   Free  → 3/شهر
     *   Basic → 20/شهر
     *   Pro+  → غير محدود
     *
     * @throws \RuntimeException إذا تجاوز الحد أو فشل الـ AI
     */
    public function execute(User $user, Job $job, string $tone = 'professional'): string
    {
        // ── Rate Limit Check ─────────────────────────────────────
     

        // إذا المستخدم لا ينتمي لشركة → نستخدم الـ free plan
        // Cover Letter للباحثين وليس للشركات
        $limit = 3; // Free default للمستخدمين العاديين

        // جلب الحد من plan المستخدم إذا وجد
        // (للمستقبل عندما يكون للمستخدمين خطط)
        if ($this->aiService->hasReachedLimit('cover_letter', $user->id, null, $limit)) {
            throw new \RuntimeException(
                "لقد وصلت للحد الشهري لتوليد رسائل التغطية ({$limit} رسائل). "
                . "الحد يُجدَّد أول كل شهر."
            );
        }

        // ── بناء الـ Prompt ──────────────────────────────────────
        $prompt = $this->buildPrompt($user, $job, $tone);

        // ── توليد الرسالة ────────────────────────────────────────
        return $this->aiService->execute(
            feature:   'cover_letter',
            prompt:    $prompt,
            options:   ['temperature' => 0.8, 'max_tokens' => 800],
           userId:$user->id,
        );
    }

    // ========================================================
    // Private Helpers
    // ========================================================

    private function buildPrompt(User $user, Job $job, string $tone): string
    {
        $user->loadMissing(['canonicalSkills', 'experiences', 'educations']);

        $applicantName = $user->display_name;
        $skills        = $user->canonicalSkills->pluck('name')->implode('، ') ?: 'غير محدد';
        $experience    = $user->experiences
            ->map(fn ($experience) => trim($experience->title . ' - ' . $experience->company_name))
            ->filter()
            ->implode('، ') ?: 'غير محدد';
        $education     = $user->educations
            ->map(fn ($education) => trim($education->degree . ' ' . $education->field_of_study . ' - ' . $education->institution_name))
            ->filter()
            ->implode('، ') ?: 'غير محدد';
        $bio           = $user->bio ?? '';

        $jobTitle       = $job->title;
        $companyName    = $job->company->company_name;
        $jobRequirements = mb_substr($job->requirements ?? $job->description, 0, 1000);

        $toneInstruction = match ($tone) {
            'formal'     => 'رسمية ومحترفة جداً',
            'friendly'   => 'ودية ومتحمسة',
            'concise'    => 'مختصرة ومباشرة (فقرتان)',
            default      => 'احترافية ومقنعة',
        };

        return <<<PROMPT
اكتب رسالة تغطية {$toneInstruction} باللغة العربية للمعلومات التالية:

**المتقدم:**
- الاسم: {$applicantName}
- المهارات: {$skills}
- الخبرة: {$experience}
- التعليم: {$education}
- نبذة: {$bio}

**الوظيفة:**
- المسمى: {$jobTitle}
- الشركة: {$companyName}
- المتطلبات: {$jobRequirements}

**تعليمات:**
- اكتب رسالة بين 150-250 كلمة
- تبدأ بتحية رسمية
- تُبرز توافق مهارات المتقدم مع متطلبات الوظيفة
- تنتهي بطلب المقابلة
- لا تكتب عنوان أو تاريخ — فقط نص الرسالة
- استخدم أسلوب مباشر وواثق
PROMPT;
    }
}
