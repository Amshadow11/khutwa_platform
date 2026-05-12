<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUsage;
use Illuminate\Support\Facades\Cache;

/**
 * SubscriptionService — الطبقة المركزية لكل منطق الاشتراكات.
 *
 * كل الـ checks تمر من هنا:
 *   - Web Controllers
 *   - Filament Admin
 *   - API (مستقبلاً)
 *   - Mobile (مستقبلاً)
 *
 * لا تكتب subscription logic في أي مكان آخر.
 */
class SubscriptionService
{
    // ========================================================
    // Cache TTL
    // ========================================================
    private const CACHE_TTL = 300; // 5 دقائق

    // ========================================================
    // جلب بيانات الاشتراك
    // ========================================================

    /**
     * جلب الاشتراك الحالي النشط للشركة.
     * مُخزَّن في Cache لتجنب N+1.
     */
    public function getActiveSubscription(Company $company): ?CompanySubscription
    {
        return Cache::remember(
            "company_subscription_{$company->id}",
            self::CACHE_TTL,
            fn() => CompanySubscription::with('plan.features')
                ->where('company_id', $company->id)
                ->where(fn($q) => $q
                    ->where('status', 'active')
                    ->orWhere('status', 'trial')
                )
                ->where(fn($q) => $q
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now())
                )
                ->latest()
                ->first()
        );
    }

    /**
     * جلب الخطة الحالية للشركة.
     * إذا لا يوجد اشتراك → خطة Free.
     */
    public function getCurrentPlan(Company $company): SubscriptionPlan
    {
        return Cache::remember(
            "company_plan_{$company->id}",
            self::CACHE_TTL,
            fn() => $this->getActiveSubscription($company)?->plan
                ?? SubscriptionPlan::with('features')
                                   ->where('slug', 'free')
                                   ->firstOrFail()
        );
    }

    /**
     * جلب قيمة feature من الخطة الحالية.
     */
    public function getFeature(Company $company, string $key, mixed $default = null): mixed
    {
        return $this->getCurrentPlan($company)->getFeature($key, $default);
    }

    // ========================================================
    // Feature Checks
    // ========================================================

    /**
     * هل الشركة مشتركة في خطة مدفوعة؟
     */
    public function isPaid(Company $company): bool
    {
        $subscription = $this->getActiveSubscription($company);
        return $subscription && ! $subscription->plan->isFree();
    }

    /**
     * هل يمكن للشركة نشر وظيفة جديدة؟
     */
    public function canPostJob(Company $company): bool
    {
        $limit = (int) $this->getFeature($company, 'max_jobs_per_month', 2);

        if ($limit === -1) return true; // غير محدود

        $used = $this->getMonthlyUsage($company, 'max_jobs_per_month');
        return $used < $limit;
    }

    /**
     * هل يمكن للشركة نشر وظيفة مميزة (featured)؟
     */
    public function canPostFeatured(Company $company): bool
    {
        $limit = (int) $this->getFeature($company, 'featured_jobs', 0);

        if ($limit === 0)  return false;
        if ($limit === -1) return true;

        $used = $this->getMonthlyUsage($company, 'featured_jobs');
        return $used < $limit;
    }

    /**
     * هل يمكن للشركة نشر وظيفة عاجلة (urgent)؟
     */
    public function canPostUrgent(Company $company): bool
    {
        $value = $this->getFeature($company, 'urgent_jobs', 'false');
        return in_array($value, ['true', '1', '-1']);
    }

    /**
     * هل تجاوزت الشركة حد الرسائل هذا الشهر؟
     */
    public function canSendMessage(Company $company): bool
    {
        $limit = (int) $this->getFeature($company, 'messaging_limit', 20);

        if ($limit === -1) return true;

        $used = $this->getMonthlyUsage($company, 'messaging_limit');
        return $used < $limit;
    }

    /**
     * هل يمكن الوصول لـ Analytics؟
     */
    public function hasAnalytics(Company $company): bool
    {
        $value = $this->getFeature($company, 'analytics', 'false');
        return in_array($value, ['true', '1']);
    }

    /**
     * هل يمكن الوصول لـ AI Matching؟
     */
    public function hasAiMatching(Company $company): bool
    {
        $value = $this->getFeature($company, 'ai_matching', 'false');
        return in_array($value, ['true', '1']);
    }

    // ========================================================
    // Usage Tracking
    // ========================================================

    /**
     * جلب الاستهلاك الشهري لـ feature معينة.
     */
    public function getMonthlyUsage(Company $company, string $featureKey): int
    {
        $period = now()->format('Y-m');

        return SubscriptionUsage::where('company_id', $company->id)
            ->where('feature_key', $featureKey)
            ->where('period', $period)
            ->value('used') ?? 0;
    }

    /**
     * زيادة عداد الاستهلاك لـ feature معينة.
     */
    public function incrementUsage(Company $company, string $featureKey): void
    {
        $period = now()->format('Y-m');

        $record = SubscriptionUsage::firstOrCreate(
            [
                'company_id'  => $company->id,
                'feature_key' => $featureKey,
                'period'      => $period,
            ],
            ['used' => 0]
        );

        $record->increment('used');
    }

    // ========================================================
    // Plan Management
    // ========================================================

    /**
     * تفعيل اشتراك جديد للشركة.
     * يُستخدم من: Admin Panel / Payment Webhook.
     */
    public function activateSubscription(
        Company $company,
        SubscriptionPlan $plan,
        int $months = 1,
        array $options = []
    ): CompanySubscription {
        // إلغاء الاشتراك الحالي إذا وجد
        CompanySubscription::where('company_id', $company->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $subscription = CompanySubscription::create([
            'company_id'        => $company->id,
            'plan_id'           => $plan->id,
            'status'            => 'active',
            'starts_at'         => now(),
            'ends_at'           => now()->addMonths($months),
            'payment_method'    => $options['payment_method'] ?? 'manual',
            'payment_reference' => $options['payment_reference'] ?? null,
            'amount_paid'       => $options['amount_paid'] ?? $plan->price,
            'notes'             => $options['notes'] ?? null,
        ]);

        // تحديث الحقول القديمة في companies للتوافق العكسي
        $company->update([
            'subscription'      => true,
            'subscription_plan' => $plan->slug,
            'subscription_end'  => now()->addMonths($months),
        ]);

        // إبطال Cache
        $this->clearCache($company);

        return $subscription;
    }

    /**
     * تفعيل Trial للشركة.
     */
    public function startTrial(Company $company, SubscriptionPlan $plan): CompanySubscription
    {
        $trialDays = $plan->trial_days > 0 ? $plan->trial_days : 7;

        $subscription = CompanySubscription::create([
            'company_id'    => $company->id,
            'plan_id'       => $plan->id,
            'status'        => 'trial',
            'starts_at'     => now(),
            'ends_at'       => now()->addDays($trialDays),
            'trial_ends_at' => now()->addDays($trialDays),
        ]);

        $this->clearCache($company);

        return $subscription;
    }

    /**
     * إبطال Cache الاشتراك للشركة.
     */
    public function clearCache(Company $company): void
    {
        Cache::forget("company_subscription_{$company->id}");
        Cache::forget("company_plan_{$company->id}");
    }

    // ========================================================
    // Usage Summary (للـ Dashboard)
    // ========================================================

    /**
     * ملخص الاستهلاك الشهري للشركة.
     */
   public function getUsageSummary(Company $company): array
    {
        $plan   = $this->getCurrentPlan($company);
        $period = now()->format('Y-m');

        $usage = SubscriptionUsage::where('company_id', $company->id)
            ->where('period', $period)
            ->pluck('used', 'feature_key');

        $jobLimit      = $plan->getFeatureInt('max_jobs_per_month', 2);
        $featuredLimit = $plan->getFeatureInt('featured_jobs', 0);
        $msgLimit      = $plan->getFeatureInt('messaging_limit', 20);

        return [
            'plan' => [
                'name'    => $plan->name,
                'slug'    => $plan->slug,
                'is_free' => $plan->isFree(),
            ],
            'jobs' => [
                'used'      => $usage->get('max_jobs_per_month', 0),
                'limit'     => $jobLimit,
                'unlimited' => $jobLimit === -1,
                'remaining' => $jobLimit === -1 ? 9999 : max(0, $jobLimit - $usage->get('max_jobs_per_month', 0)),
            ],
            'featured' => [
                'used'      => $usage->get('featured_jobs', 0),
                'limit'     => $featuredLimit,
                'unlimited' => $featuredLimit === -1,
            ],
            'messages' => [
                'used'      => $usage->get('messaging_limit', 0),
                'limit'     => $msgLimit,
                'unlimited' => $msgLimit === -1,
            ],
        ];
    }
}