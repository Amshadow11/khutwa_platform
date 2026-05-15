<?php

namespace App\Actions\Subscription;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPlan;
use App\Notifications\TrialStarted;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;

class StartTrialAction
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * تفعيل التجربة المجانية للشركة.
     *
     * Guards:
     *   1. الخطة لديها trial_days > 0
     *   2. الشركة لم تستخدم trial من قبل
     *   3. الشركة ليس لديها اشتراك نشط
     *   4. لا يوجد طلب ترقية pending
     *
     * @throws \RuntimeException
     */
    public function execute(Company $company, SubscriptionPlan $plan): CompanySubscription
    {
        // ── Guard 1: الخطة تدعم Trial ───────────────────────────
        if ($plan->trial_days <= 0) {
            throw new \RuntimeException('هذه الخطة لا تدعم التجربة المجانية.');
        }

        // ── Guard 2: لم تُستخدم Trial من قبل ───────────────────
        if ($company->hasUsedTrial()) {
            throw new \RuntimeException(
                'استخدمت التجربة المجانية من قبل. التجربة متاحة مرة واحدة فقط لكل شركة.'
            );
        }

        // ── Guard 3: لا اشتراك نشط ──────────────────────────────
        $activeSubscription = $this->subscriptionService->getActiveSubscription($company);
        if ($activeSubscription) {
            throw new \RuntimeException('لديك اشتراك نشط بالفعل.');
        }

        // ── Guard 4: لا طلب pending ─────────────────────────────
        if (\App\Models\SubscriptionUpgradeRequest::hasPendingRequest($company->id)) {
            throw new \RuntimeException('لديك طلب ترقية قيد المراجعة. يرجى إلغاؤه أولاً.');
        }

        // ── تفعيل Trial + تسجيل الاستخدام ───────────────────────
        $subscription = DB::transaction(function () use ($company, $plan) {
            $trialDays = $plan->trial_days;

            $subscription = CompanySubscription::create([
                'company_id'    => $company->id,
                'plan_id'       => $plan->id,
                'status'        => 'trial',
                'starts_at'     => now(),
                'ends_at'       => now()->addDays($trialDays),
                'trial_ends_at' => now()->addDays($trialDays),
            ]);

            // تسجيل أن الشركة استخدمت التجربة
            $company->update(['trial_used_at' => now()]);

            return $subscription;
        });

        // إبطال Cache + إشعار — خارج Transaction
        $this->subscriptionService->clearCache($company);
        $company->notify(new TrialStarted($subscription));

        return $subscription;
    }
}