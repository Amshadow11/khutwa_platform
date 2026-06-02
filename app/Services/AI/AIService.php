<?php

namespace App\Services\AI;

use App\Models\AIUsageLog;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Log;

/**
 * AIService — Entry Point موحد لجميع AI features.
 *
 * كل الكود يمر من هنا — لا تستدعي OpenAI أو Gemini مباشرة.
 *
 * المسؤوليات:
 *   1. اختيار الـ Provider الصحيح لكل feature
 *   2. Rate Limiting (هل تجاوز المستخدم الحد؟)
 *   3. تسجيل الاستخدام في ai_usage_logs
 *   4. التعامل مع الأخطاء بشكل موحد
 */
class AIService
{
    public function __construct(
        private readonly OpenAIProvider $openai,
        private readonly GeminiProvider $gemini,
    ) {}

    // ========================================================
    // Rate Limiting
    // ========================================================

    /**
     * هل تجاوز المستخدم حد الاستخدام الشهري؟
     *
     * @param string   $feature    اسم الميزة (cover_letter, job_matching, ...)
     * @param int|null $userId     للباحثين
     * @param int|null $companyId  للشركات
     * @param int      $limit      الحد الشهري (-1 = غير محدود)
     */
    public function hasReachedLimit(
        string $feature,
        ?int $userId,
        ?int $companyId,
        int $limit
    ): bool {
        if ($limit === -1) return false;

        $used = $userId
            ? AIUsageLog::monthlyUsageForUser($userId, $feature)
            : AIUsageLog::monthlyUsageForCompany($companyId, $feature);

        return $used >= $limit;
    }

    // ========================================================
    // Core Execute Method
    // ========================================================

    /**
     * تنفيذ AI request مع logging تلقائي.
     *
     * @param string   $feature   اسم الميزة
     * @param string   $prompt    النص المُرسَل
     * @param array    $options   خيارات إضافية
     * @param int|null $userId
     * @param int|null $companyId
     *
     * @throws \RuntimeException إذا فشل الطلب
     *
     * @return string النص المُولَّد
     */
    public function execute(
        string $feature,
        string $prompt,
        array $options = [],
        ?int $userId = null,
        ?int $companyId = null,
    ): string {
        $provider   = $this->getProvider($feature);
        $startTime  = microtime(true);

        try {
            $result     = $provider->complete($prompt, $options);
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            // تسجيل الاستخدام
            AIUsageLog::logSuccess(
                feature:           $feature,
                model:             $result['model'],
                promptTokens:      $result['prompt_tokens'],
                completionTokens:  $result['completion_tokens'],
                durationMs:        $durationMs,
                userId:            $userId,
                companyId:         $companyId,
            );

            return $result['content'];

        } catch (\Throwable $e) {
            AIUsageLog::logFailure(
                feature:      $feature,
                model:        $provider->getModel(),
                errorMessage: $e->getMessage(),
                userId:       $userId,
                companyId:    $companyId,
            );

            Log::error("AIService: فشل في {$feature}", [
                'error'      => $e->getMessage(),
                'user_id'    => $userId,
                'company_id' => $companyId,
            ]);

            throw new \RuntimeException(
                "حدث خطأ في معالجة طلبك. حاول مجدداً.",
                0,
                $e
            );
        }
    }

    /**
     * تنفيذ AI request مع System Message.
     */
    public function executeWithSystem(
        string $feature,
        string $system,
        string $prompt,
        array $options = [],
        ?int $userId = null,
        ?int $companyId = null,
    ): string {
        $provider   = $this->getProvider($feature);
        $startTime  = microtime(true);

        try {
            $result     = $provider->completeWithSystem($system, $prompt, $options);
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            AIUsageLog::logSuccess(
                feature:          $feature,
                model:            $result['model'],
                promptTokens:     $result['prompt_tokens'],
                completionTokens: $result['completion_tokens'],
                durationMs:       $durationMs,
                userId:           $userId,
                companyId:        $companyId,
            );

            return $result['content'];

        } catch (\Throwable $e) {
            AIUsageLog::logFailure(
                feature:      $feature,
                model:        $provider->getModel(),
                errorMessage: $e->getMessage(),
                userId:       $userId,
                companyId:    $companyId,
            );

            throw new \RuntimeException("حدث خطأ في معالجة طلبك. حاول مجدداً.", 0, $e);
        }
    }

    // ========================================================
    // Provider Selection
    // ========================================================

    /**
     * اختيار الـ Provider المناسب لكل feature.
     */
    private function getProvider(string $feature): OpenAIProvider|GeminiProvider
    {
        $providerName = config("ai.features.{$feature}", 'gemini');

        return match ($providerName) {
            'openai' => $this->openai,
            'gemini' => $this->gemini,
            default  => $this->gemini,
        };
    }
}