<?php

namespace App\Actions\Subscription;

use App\Enums\UpgradeRequestStatus;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\User;
use App\Notifications\SubscriptionRejected;
use Illuminate\Support\Facades\DB;

class RejectSubscriptionAction
{
    /**
     * رفض طلب الترقية.
     *
     * الخطوات:
     *   1. التحقق أن الطلب pending
     *   2. تحديث الطلب إلى rejected داخل Transaction
     *   3. إشعار الشركة بسبب الرفض
     *
     * @param SubscriptionUpgradeRequest $upgradeRequest طلب الترقية
     * @param User|null $rejectedBy الأدمن الذي رفض (null = تلقائي/scheduler)
     * @param string|null $rejectionReason سبب الرفض — يُرسل للشركة
     * @param string|null $adminNotes ملاحظات داخلية — لا تُرسل للشركة
     * @param bool $isAutomatic هل الرفض تلقائي من scheduler؟
     *
     * @throws \RuntimeException إذا الطلب ليس pending
     */
    public function execute(
        SubscriptionUpgradeRequest $upgradeRequest,
        ?User $rejectedBy = null,
        ?string $rejectionReason = null,
        ?string $adminNotes = null,
        bool $isAutomatic = false
    ): void {
        // ── Guard: الطلب يجب أن يكون pending ──────────────────
        if (! $upgradeRequest->isPending()) {
            throw new \RuntimeException(
                "لا يمكن رفض طلب بحالة: {$upgradeRequest->status->label()}"
            );
        }

        // ── Transaction: تحديث حالة الطلب ──────────────────────
        DB::transaction(function () use ($upgradeRequest, $rejectedBy, $rejectionReason, $adminNotes, $isAutomatic) {
            $upgradeRequest->update([
                'status'           => UpgradeRequestStatus::Rejected->value,
                'rejected_by'      => $rejectedBy?->id,
                'rejected_at'      => now(),
                'rejection_reason' => $rejectionReason ?? 'لم يتم تحديد سبب.',
                'admin_notes'      => $adminNotes,
            ]);
        });

        // إشعار الشركة — خارج Transaction
        // لا نُرسل إشعار إذا كان الرفض تلقائياً من الـ scheduler
        // (الشركة ستتلقى إشعاراً منفصلاً عند انتهاء الصلاحية)
        if (! $isAutomatic) {
            $upgradeRequest->company->notify(
                new SubscriptionRejected($upgradeRequest)
            );
        }
    }
}