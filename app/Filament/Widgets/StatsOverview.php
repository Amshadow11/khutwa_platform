<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Job;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeSubscriptions = CompanySubscription::where('status', 'active')
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->count();

        $trialSubscriptions = CompanySubscription::where('status', 'trial')
            ->where('trial_ends_at', '>', now())
            ->count();

        $pendingRequests = SubscriptionUpgradeRequest::pending()->count();

        return [
            Stat::make('إجمالي المستخدمين', User::count())
                ->description('باحثو العمل المسجّلون')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),

            Stat::make('الشركات النشطة', Company::where('status', 'active')->where('is_verified', true)->count())
                ->description('من إجمالي ' . Company::count() . ' شركة')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('success'),

            Stat::make('شركات قيد المراجعة', Company::where('status', 'pending')->count())
                ->description('تنتظر الموافقة')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('الوظائف النشطة', Job::where('status', 'active')->count())
                ->description('من إجمالي ' . Job::count() . ' وظيفة')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('success'),

            Stat::make('اشتراكات مدفوعة', $activeSubscriptions)
                ->description($trialSubscriptions . ' في فترة تجربة')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('primary'),

            Stat::make('طلبات ترقية معلّقة', $pendingRequests)
                ->description($pendingRequests > 0 ? 'تحتاج مراجعة' : 'لا يوجد طلبات')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color($pendingRequests > 0 ? 'warning' : 'gray'),
        ];
    }
}