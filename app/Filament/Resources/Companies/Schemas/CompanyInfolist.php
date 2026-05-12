<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\Company;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('company_name')
                    ->label('اسم الشركة'),

                TextEntry::make('email')
                    ->label('البريد الإلكتروني'),

                TextEntry::make('phone')
                    ->label('رقم الهاتف')
                    ->placeholder('-'),

                TextEntry::make('phone_code')
                    ->label('رمز الدولة')
                    ->placeholder('-'),

                TextEntry::make('logo')
                    ->label('شعار الشركة')
                    ->placeholder('-'),

                TextEntry::make('profile_picture')
                    ->label('الصورة الشخصية')
                    ->placeholder('-'),

                TextEntry::make('description')
                    ->label('وصف الشركة')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('address')
                    ->label('العنوان')
                    ->placeholder('-'),

                TextEntry::make('website')
                    ->label('الموقع الإلكتروني')
                    ->placeholder('-'),

                TextEntry::make('industry')
                    ->label('القطاع')
                    ->placeholder('-'),

                TextEntry::make('founded_year')
                    ->label('سنة التأسيس')
                    ->placeholder('-'),

                TextEntry::make('company_size')
                    ->label('حجم الشركة')
                    ->badge(),

                IconEntry::make('subscription')
                    ->label('يوجد اشتراك')
                    ->boolean(),

                TextEntry::make('subscription_started')
                    ->label('تاريخ بداية الاشتراك')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('subscription_end')
                    ->label('تاريخ انتهاء الاشتراك')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('status')
                    ->label('حالة الشركة')
                    ->badge(),

                IconEntry::make('is_verified')
                    ->label('الحساب موثّق')
                    ->boolean(),

                TextEntry::make('views')
                    ->label('عدد المشاهدات')
                    ->numeric(),

                TextEntry::make('role')
                    ->label('الدور'),

                TextEntry::make('last_login')
                    ->label('آخر تسجيل دخول')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('deleted_at')
                    ->label('تاريخ الحذف')
                    ->dateTime()
                    ->visible(fn (Company $record): bool => $record->trashed()),

                TextEntry::make('activeSubscription.plan.name')
                    ->label('الخطة الحالية')
                    ->badge()
                    ->placeholder('مجاني'),

                TextEntry::make('activeSubscription.status')
                    ->label('حالة الاشتراك الحالية')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'trial'  => 'info',
                        default  => 'gray',
                    })
                    ->placeholder('لا يوجد اشتراك'),

                TextEntry::make('activeSubscription.ends_at')
                    ->label('ينتهي الاشتراك في')
                    ->date()
                    ->placeholder('—'),

            ]);
    }
}