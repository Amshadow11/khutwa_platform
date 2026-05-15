<?php

namespace App\Filament\Resources\UpgradeRequests\Tables;

use App\Actions\Subscription\ApproveSubscriptionAction;
use App\Actions\Subscription\RejectSubscriptionAction;
use App\Enums\UpgradeRequestStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UpgradeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.company_name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fromPlan.name')
                    ->label('من خطة')
                    ->placeholder('مجاني')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('toPlan.name')
                    ->label('إلى خطة')
                    ->badge()
                    ->color(fn($record) => match($record->toPlan?->slug) {
                        'basic'      => 'info',
                        'pro'        => 'success',
                        'enterprise' => 'warning',
                        default      => 'gray',
                    }),

                TextColumn::make('months')
                    ->label('المدة')
                    ->formatStateUsing(fn($state) => $state . ' شهر'),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    // يستخدم Enum مباشرة — لا string literals
                   ->color(fn($state) => $state?->color() ?? 'gray')
                   ->formatStateUsing(fn($state) => $state?->label() ?? $state),
                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('تنتهي في')
                    ->dateTime()
                    ->placeholder('—')
                    ->color(fn($record) => $record->isExpired() ? 'danger' : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approvedBy.full_name')
                    ->label('وافق عليه')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('rejectedBy.full_name')
                    ->label('رفضه')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(UpgradeRequestStatus::filamentOptions()),

                SelectFilter::make('toPlan')
                    ->label('الخطة المطلوبة')
                    ->relationship('toPlan', 'name'),
            ])

            ->recordActions([
                ViewAction::make()->label('عرض'),

                // ✅ الموافقة — تمر بـ ApproveSubscriptionAction
                Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('الموافقة على طلب الترقية')
                    ->modalDescription(fn($record) => "هل أنت متأكد من الموافقة على ترقية شركة \"{$record->company->company_name}\" إلى خطة \"{$record->toPlan->name}\"؟ سيتم تفعيل الاشتراك فوراً.")
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('ملاحظات داخلية (اختياري)')
                            ->placeholder('ملاحظات لا تُرسَل للشركة')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $admin = auth()->user();
                        app(ApproveSubscriptionAction::class)->execute(
                            $record,
                            $admin,
                            $data['admin_notes'] ?? null
                        );
                    })
                    ->successNotificationTitle('تمت الموافقة وتفعيل الاشتراك'),

                // ❌ الرفض — تمر بـ RejectSubscriptionAction
                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('رفض طلب الترقية')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->placeholder('سيُرسَل هذا السبب للشركة في الإشعار')
                            ->required()
                            ->rows(3),

                        Textarea::make('admin_notes')
                            ->label('ملاحظات داخلية (اختياري)')
                            ->placeholder('لا تُرسَل للشركة')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $admin = auth()->user();
                        app(RejectSubscriptionAction::class)->execute(
                            $record,
                            $admin,
                            $data['rejection_reason'],
                            $data['admin_notes'] ?? null
                        );
                    })
                    ->successNotificationTitle('تم رفض الطلب وإشعار الشركة'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                DeleteBulkAction::make(),
                ]),

            ])

            ->defaultSort('created_at', 'desc');
    }
}