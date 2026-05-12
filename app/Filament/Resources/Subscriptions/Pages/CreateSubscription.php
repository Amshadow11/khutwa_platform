<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Services\SubscriptionService;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * بعد إنشاء الاشتراك — نحدّث الشركة ونبطل الـ Cache.
     */
    protected function afterCreate(): void
    {
        $subscription = $this->record;
        $company      = $subscription->company;
        $plan         = $subscription->plan;

        // تحديث الحقول القديمة للتوافق العكسي
        if ($plan && $subscription->status === 'active') {
            $company->update([
                'subscription'      => ! $plan->isFree(),
                'subscription_plan' => $plan->slug,
                'subscription_end'  => $subscription->ends_at,
            ]);
        }

        app(SubscriptionService::class)->clearCache($company);
    }
}