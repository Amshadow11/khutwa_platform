<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company.company_name')
                    ->label('الشركة'),

                TextEntry::make('plan.name')
                    ->label('الخطة')
                    ->badge(),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'active'    => 'success',
                        'trial'     => 'info',
                        'cancelled' => 'danger',
                        'expired'   => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'active'    => 'نشط',
                        'trial'     => 'تجربة',
                        'cancelled' => 'ملغي',
                        'expired'   => 'منتهي',
                        'pending'   => 'معلّق',
                        default     => $state,
                    }),

                TextEntry::make('amount_paid')
                    ->label('المبلغ المدفوع')
                    ->money('USD'),

                TextEntry::make('payment_method')
                    ->label('طريقة الدفع')
                    ->placeholder('-'),

                TextEntry::make('payment_reference')
                    ->label('رقم المرجع')
                    ->placeholder('-'),

                TextEntry::make('starts_at')
                    ->label('بداية الاشتراك')
                    ->dateTime(),

                TextEntry::make('ends_at')
                    ->label('انتهاء الاشتراك')
                    ->dateTime()
                    ->placeholder('غير محدود'),

                TextEntry::make('trial_ends_at')
                    ->label('انتهاء التجربة')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('cancelled_at')
                    ->label('تاريخ الإلغاء')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ]);
    }
}