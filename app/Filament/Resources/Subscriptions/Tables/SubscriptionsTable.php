<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Models\CompanySubscription;
use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.company_name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plan.name')
                    ->label('الخطة')
                    ->badge()
                    ->color(fn($record) => match($record->plan?->slug) {
                        'free'       => 'gray',
                        'basic'      => 'info',
                        'pro'        => 'success',
                        'enterprise' => 'warning',
                        default      => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'active'    => 'success',
                        'trial'     => 'info',
                        'cancelled' => 'danger',
                        'expired'   => 'warning',
                        'pending'   => 'gray',
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

                TextColumn::make('amount_paid')
                    ->label('المبلغ')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('بداية')
                    ->date()
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('انتهاء')
                    ->date()
                    ->sortable()
                    ->placeholder('غير محدود'),

                TextColumn::make('trial_ends_at')
                    ->label('انتهاء التجربة')
                    ->date()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'    => 'نشط',
                        'trial'     => 'تجربة',
                        'cancelled' => 'ملغي',
                        'expired'   => 'منتهي',
                        'pending'   => 'معلّق',
                    ]),

                SelectFilter::make('plan')
                    ->label('الخطة')
                    ->relationship('plan', 'name'),
            ])

            ->recordActions([
                ViewAction::make()->label('عرض'),

                // ✅ تفعيل الاشتراك
                Action::make('activate')
                    ->label('تفعيل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->modalHeading('تفعيل الاشتراك')
                    ->action(function ($record) {
                        $record->update(['status' => 'active']);
                        app(SubscriptionService::class)->clearCache($record->company);
                    })
                    ->successNotificationTitle('تم تفعيل الاشتراك'),

                // ❌ إلغاء الاشتراك
                Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => in_array($record->status, ['active', 'trial']))
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء الاشتراك')
                    ->modalDescription('هل أنت متأكد؟ ستفقد الشركة مميزات الخطة المدفوعة.')
                    ->action(function ($record) {
                        $record->update([
                            'status'       => 'cancelled',
                            'cancelled_at' => now(),
                        ]);
                        app(SubscriptionService::class)->clearCache($record->company);
                    })
                    ->successNotificationTitle('تم إلغاء الاشتراك'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('created_at', 'desc');
    }
}