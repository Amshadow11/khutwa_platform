<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
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

            Stat::make('إجمالي الطلبات', Application::count())
                ->description('طلبات التوظيف المستلمة')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('طلبات اليوم', Application::whereDate('created_at', today())->count())
                ->description('طلبات جديدة اليوم')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),
        ];
    }
}