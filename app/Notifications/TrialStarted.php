<?php

namespace App\Notifications;

use App\Models\CompanySubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار للشركة عند تفعيل التجربة المجانية.
 */
class TrialStarted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CompanySubscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $planName  = $this->subscription->plan->name;
        $trialDays = $this->subscription->trial_ends_at->diffInDays(now());
        $endsAt    = $this->subscription->trial_ends_at->format('d/m/Y');

        return [
            'type'            => 'trial_started',
            'message'         => "🎉 بدأت تجربتك المجانية لخطة \"{$planName}\" لمدة {$trialDays} يوم",
            'subscription_id' => $this->subscription->id,
            'plan_name'       => $planName,
            'trial_ends_at'   => $endsAt,
            'url'             => route('company.subscription.index'),
        ];
    }
}