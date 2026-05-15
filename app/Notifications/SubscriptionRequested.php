<?php

namespace App\Notifications;

use App\Models\SubscriptionUpgradeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار للأدمن عند تلقي طلب ترقية اشتراك جديد.
 *
 * يُرسَل لـ: جميع المستخدمين الذين role = 'admin'
 * القناة: database (يظهر في notification bell)
 *
 * مستقبلاً: يمكن إضافة mail channel للإشعار الفوري
 */
class SubscriptionRequested extends Notification implements ShouldQueue
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
        $companyName  = $this->upgradeRequest->company->company_name;
        $fromPlan     = $this->upgradeRequest->fromPlan?->name ?? 'مجاني';
        $toPlan       = $this->upgradeRequest->toPlan->name;
        $months       = $this->upgradeRequest->months;
        $amount       = number_format($this->upgradeRequest->amount, 2);

        return [
            'type'               => 'subscription_requested',
            'message'            => "طلبت شركة \"{$companyName}\" الترقية من {$fromPlan} إلى {$toPlan}",
            'upgrade_request_id' => $this->upgradeRequest->id,
            'company_id'         => $this->upgradeRequest->company_id,
            'company_name'       => $companyName,
            'from_plan'          => $fromPlan,
            'to_plan'            => $toPlan,
            'months'             => $months,
            'amount'             => $amount,
            // رابط مباشر للطلب في Filament Admin
            'url'                => '/admin/upgrade-requests/' . $this->upgradeRequest->id,
        ];
    }
}