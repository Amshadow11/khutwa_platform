<?php

namespace App\Notifications;

use App\Models\SubscriptionUpgradeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار للشركة عند رفض طلب الترقية.
 *
 * يُرسَل لـ: الشركة (Company model — Notifiable)
 * القناة: database
 *
 * يحتوي: سبب الرفض حتى الشركة تعرف ماذا تفعل
 */
class SubscriptionRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SubscriptionUpgradeRequest $upgradeRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $planName        = $this->upgradeRequest->toPlan->name;
        $rejectionReason = $this->upgradeRequest->rejection_reason ?? 'لم يتم تحديد سبب.';

        return [
            'type'               => 'subscription_rejected',
            'message'            => "تم رفض طلب الترقية إلى خطة \"{$planName}\"",
            'upgrade_request_id' => $this->upgradeRequest->id,
            'plan_name'          => $planName,
            'rejection_reason'   => $rejectionReason,
            // الشركة يمكنها إعادة الطلب مجدداً
            'url'                => route('company.subscription.index'),
        ];
    }
}