<?php

namespace App\Services;

use App\Actions\Subscription\RequestUpgradeAction;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\SubscriptionUsage;
use App\Enums\UpgradeRequestStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    private const CACHE_TTL = 300;

    // ========================================================
    // جلب بيانات الاشتراك
    // ========================================================

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

    public function getCurrentPlan(Company $company): SubscriptionPlan
    {
        return Cache::remember(
            "company_plan_{$company->id}",
            self::CACHE_TTL,
            function () use ($company) {
                $subscription = $this->getActiveSubscription($company);
                if ($subscription?->plan) {
                    return $subscription->plan;
                }

                $freePlan = SubscriptionPlan::with('features')
                    ->where('slug', 'free')
                    ->first();

                if (! $freePlan) {
                    Log::warning('SubscriptionService: Free plan not found. Run SubscriptionPlansSeeder.');
                    $freePlan = new SubscriptionPlan([
                        'name'  => 'مجاني',
                        'slug'  => 'free',
                        'price' => 0,
                    ]);
                    $freePlan->setRelation('features', collect());
                }

                return $freePlan;
            }
        );
    }

    public function getFeature(Company $company, string $key, mixed $default = null): mixed
    {
        return $this->getCurrentPlan($company)->getFeature($key, $default);
    }

    // ========================================================
    // Feature Checks
    // ========================================================

    public function isPaid(Company $company): bool
    {
        $subscription = $this->getActiveSubscription($company);
        return $subscription && ! $subscription->plan->isFree();
    }

    public function canPostJob(Company $company): bool
    {
        $limit = (int) $this->getFeature($company, 'max_jobs_per_month', 2);
        if ($limit === -1) return true;
        return $this->getMonthlyUsage($company, 'max_jobs_per_month') < $limit;
    }

    public function canPostFeatured(Company $company): bool
    {
        $limit = (int) $this->getFeature($company, 'featured_jobs', 0);
        if ($limit === 0)  return false;
        if ($limit === -1) return true;
        return $this->getMonthlyUsage($company, 'featured_jobs') < $limit;
    }

    public function canPostUrgent(Company $company): bool
    {
        $value = $this->getFeature($company, 'urgent_jobs', 'false');
        return in_array($value, ['true', '1', '-1']);
    }

    public function canSendMessage(Company $company): bool
    {
        $limit = (int) $this->getFeature($company, 'messaging_limit', 20);
        if ($limit === -1) return true;
        return $this->getMonthlyUsage($company, 'messaging_limit') < $limit;
    }

    public function hasAnalytics(Company $company): bool
    {
        return in_array($this->getFeature($company, 'analytics', 'false'), ['true', '1']);
    }

    public function hasAiMatching(Company $company): bool
    {
        return in_array($this->getFeature($company, 'ai_matching', 'false'), ['true', '1']);
    }

    // ========================================================
    // Usage Tracking
    // ========================================================

    public function getMonthlyUsage(Company $company, string $featureKey): int
    {
        return SubscriptionUsage::where('company_id', $company->id)
            ->where('feature_key', $featureKey)
            ->where('period', now()->format('Y-m'))
            ->value('used') ?? 0;
    }

    public function incrementUsage(Company $company, string $featureKey): void
    {
        $period = now()->format('Y-m');

        DB::transaction(function () use ($company, $featureKey, $period) {
            $record = SubscriptionUsage::where('company_id', $company->id)
                ->where('feature_key', $featureKey)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if ($record) {
                $record->increment('used');
            } else {
                SubscriptionUsage::create([
                    'company_id'  => $company->id,
                    'feature_key' => $featureKey,
                    'period'      => $period,
                    'used'        => 1,
                ]);
            }
        });
    }

    // ========================================================
    // Plan Management
    // ========================================================

    public function activateSubscription(
        Company $company,
        SubscriptionPlan $plan,
        int $months = 1,
        array $options = []
    ): CompanySubscription {
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

        $company->update([
            'subscription'      => true,
            'subscription_plan' => $plan->slug,
            'subscription_end'  => now()->addMonths($months),
        ]);

        $this->clearCache($company);

        return $subscription;
    }

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

    // ========================================================
    // Upgrade Request Management — Entry Points
    // ========================================================

    /**
     * إنشاء طلب ترقية جديد.
     * Entry point للـ Controller — يفوّض للـ Action.
     *
     * @throws \RuntimeException من RequestUpgradeAction
     */
    public function requestUpgrade(
        Company $company,
        SubscriptionPlan $toPlan,
        int $months = 1,
        ?string $notes = null
    ): SubscriptionUpgradeRequest {
        return app(RequestUpgradeAction::class)->execute(
            $company, $toPlan, $months, $notes
        );
    }

    /**
     * إلغاء طلب الترقية من الشركة نفسها.
     *
     * @throws \RuntimeException إذا الطلب ليس pending أو لا يخص الشركة
     */
    public function cancelUpgradeRequest(
        Company $company,
        SubscriptionUpgradeRequest $upgradeRequest
    ): void {
        // التحقق أن الطلب يخص هذه الشركة
        if ($upgradeRequest->company_id !== $company->id) {
            throw new \RuntimeException('غير مصرح لك بإلغاء هذا الطلب.');
        }

        if (! $upgradeRequest->canBeCancelledByCompany()) {
            throw new \RuntimeException(
                "لا يمكن إلغاء طلب بحالة: {$upgradeRequest->status->label()}"
            );
        }

        DB::transaction(function () use ($upgradeRequest) {
            $upgradeRequest->update([
                'status'       => UpgradeRequestStatus::Cancelled->value,
                'cancelled_at' => now(),
            ]);
        });
    }
        /**
     * تفعيل التجربة المجانية.
     * Entry point للـ Controller — يفوّض للـ Action.
     *
     * @throws \RuntimeException من StartTrialAction
     */
    public function startFreeTrial(Company $company, SubscriptionPlan $plan): CompanySubscription
    {
        return app(\App\Actions\Subscription\StartTrialAction::class)->execute($company, $plan);
    }

    /**
     * جلب الطلب الـ pending الحالي للشركة.
     */
    public function getPendingRequest(Company $company): ?SubscriptionUpgradeRequest
    {
        return SubscriptionUpgradeRequest::getPendingRequest($company->id);
    }

    // ========================================================
    // Cache
    // ========================================================

    public function clearCache(Company $company): void
    {
        Cache::forget("company_subscription_{$company->id}");
        Cache::forget("company_plan_{$company->id}");
    }

    // ========================================================
    // Usage Summary
    // ========================================================

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