<?php

namespace App\Notifications;

use App\Models\CompanySubscription;
use App\Models\SubscriptionUpgradeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار للشركة عند الموافقة على طلب الترقية وتفعيل الاشتراك.
 *
 * يُرسَل لـ: الشركة (Company model — Notifiable)
 * القناة: database
 *
 * يحتوي: تفاصيل الاشتراك الجديد (تاريخ البداية والانتهاء)
 */
class SubscriptionApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SubscriptionUpgradeRequest $upgradeRequest,
        private readonly CompanySubscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $planName  = $this->upgradeRequest->toPlan->name;
        $endsAt    = $this->subscription->ends_at?->format('d/m/Y') ?? 'غير محدود';
        $startsAt  = $this->subscription->starts_at->format('d/m/Y');

        return [
            'type'               => 'subscription_approved',
            'message'            => "🎉 تمت الموافقة على اشتراكك في خطة \"{$planName}\"",
            'upgrade_request_id' => $this->upgradeRequest->id,
            'subscription_id'    => $this->subscription->id,
            'plan_name'          => $planName,
            'starts_at'          => $startsAt,
            'ends_at'            => $endsAt,
            'months'             => $this->upgradeRequest->months,
            'amount_paid'        => number_format($this->subscription->amount_paid, 2),
            // رابط صفحة الاشتراك في لوحة الشركة
            'url'                => route('company.subscription.index'),
        ];
    }
}