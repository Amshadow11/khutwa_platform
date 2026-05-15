<?php

namespace App\Filament\Resources\UpgradeRequests\Schemas;

use App\Enums\UpgradeRequestStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UpgradeRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company.company_name')
                    ->label('الشركة'),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => $state?->color() ?? 'gray')
                    ->formatStateUsing(fn($state) => $state?->label() ?? $state),
                TextEntry::make('fromPlan.name')
                    ->label('من خطة')
                    ->badge()
                    ->placeholder('مجاني'),

                TextEntry::make('toPlan.name')
                    ->label('إلى خطة')
                    ->badge(),

                TextEntry::make('months')
                    ->label('المدة')
                    ->formatStateUsing(fn($state) => $state . ' شهر'),

                TextEntry::make('amount')
                    ->label('المبلغ')
                    ->money('USD'),

                TextEntry::make('notes')
                    ->label('ملاحظات الشركة')
                    ->placeholder('لا يوجد')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime(),

                TextEntry::make('expires_at')
                    ->label('تنتهي في')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('approvedBy.full_name')
                    ->label('وافق عليه')
                    ->placeholder('—'),

                TextEntry::make('approved_at')
                    ->label('تاريخ الموافقة')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('rejectedBy.full_name')
                    ->label('رفضه')
                    ->placeholder('—'),

                TextEntry::make('rejected_at')
                    ->label('تاريخ الرفض')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('rejection_reason')
                    ->label('سبب الرفض')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('admin_notes')
                    ->label('ملاحظات الأدمن (داخلية)')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('resultingSubscription.id')
                    ->label('رقم الاشتراك الناتج')
                    ->placeholder('لم ينشأ بعد'),

                TextEntry::make('payment_reference')
                    ->label('مرجع الدفع')
                    ->placeholder('—'),
            ]);
    }
}