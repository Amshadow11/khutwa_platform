<?php

namespace App\Actions\Subscription;

use App\Enums\UpgradeRequestStatus;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\User;
use App\Notifications\SubscriptionApproved;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;

class ApproveSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * الموافقة على طلب الترقية وتفعيل الاشتراك.
     *
     * الخطوات داخل Transaction واحد:
     *   1. التحقق أن الطلب pending
     *   2. تفعيل الاشتراك عبر SubscriptionService
     *   3. تحديث الطلب إلى approved + ربطه بالاشتراك الجديد
     *
     * بعد Transaction:
     *   4. إشعار الشركة
     *
     * @param SubscriptionUpgradeRequest $upgradeRequest طلب الترقية
     * @param User|null $approvedBy الأدمن الذي وافق (null = تلقائي)
     * @param string|null $adminNotes ملاحظات الأدمن الداخلية
     * @param array $options خيارات إضافية لـ activateSubscription
     *
     * @throws \RuntimeException إذا الطلب ليس pending
     */
    public function execute(
        SubscriptionUpgradeRequest $upgradeRequest,
        ?User $approvedBy = null,
        ?string $adminNotes = null,
        array $options = []
    ): void {
        // ── Guard: الطلب يجب أن يكون pending ──────────────────
        if (! $upgradeRequest->isPending()) {
            throw new \RuntimeException(
                "لا يمكن الموافقة على طلب بحالة: {$upgradeRequest->status->label()}"
            );
        }

        // ── Transaction: الموافقة + تفعيل الاشتراك ─────────────
        $subscription = DB::transaction(function () use ($upgradeRequest, $approvedBy, $adminNotes, $options) {
            $company = $upgradeRequest->company;
            $plan    = $upgradeRequest->toPlan;

            // تفعيل الاشتراك عبر Service — هو المسؤول عن:
            // إلغاء الاشتراك الحالي + إنشاء الجديد + تحديث Cache
            $subscription = $this->subscriptionService->activateSubscription(
                $company,
                $plan,
                $upgradeRequest->months,
                array_merge([
                    'payment_method'    => $upgradeRequest->payment_method ?? 'manual',
                    'payment_reference' => $upgradeRequest->payment_reference,
                    'amount_paid'       => $upgradeRequest->amount,
                ], $options)
            );

            // تحديث الطلب
            $upgradeRequest->update([
                'status'                    => UpgradeRequestStatus::Approved->value,
                'approved_by'               => $approvedBy?->id,
                'approved_at'               => now(),
                'admin_notes'               => $adminNotes,
                'resulting_subscription_id' => $subscription->id,
            ]);

            return $subscription;
        });

        // إشعار الشركة — خارج Transaction
        $upgradeRequest->company->notify(
            new SubscriptionApproved($upgradeRequest, $subscription)
        );
    }
}