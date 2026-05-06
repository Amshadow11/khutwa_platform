<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),

                TextColumn::make('industry')
                    ->label('القطاع')
                    ->searchable(),

                TextColumn::make('company_size')
                    ->label('الحجم')
                    ->badge(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'active'   => 'success',
                        'pending'  => 'warning',
                        'inactive' => 'danger',
                        default    => 'gray',
                    }),

                IconColumn::make('is_verified')
                    ->label('متحقق')
                    ->boolean(),

                TextColumn::make('subscription_plan')
                    ->label('الاشتراك')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'pro'   => 'success',
                        'basic' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->date()
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'  => 'قيد المراجعة',
                        'active'   => 'نشطة',
                        'inactive' => 'معطّلة',
                    ]),

                SelectFilter::make('is_verified')
                    ->label('التحقق')
                    ->options([
                        '1' => 'متحقق منها',
                        '0' => 'غير متحقق',
                    ]),

                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make()->label('عرض'),

                // ✅ Approve — تفعيل الشركة
                Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'active' || ! $record->is_verified)
                    ->requiresConfirmation()
                    ->modalHeading('الموافقة على الشركة')
                    ->modalDescription('هل أنت متأكد من الموافقة على هذه الشركة؟ سيتمكن من نشر الوظائف.')
                    ->action(function ($record) {
                        $record->update([
                            'status'      => 'active',
                            'is_verified' => true,
                        ]);
                    })
                    ->successNotificationTitle('تمت الموافقة على الشركة'),

                // ❌ Reject — رفض الشركة
                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'inactive')
                    ->requiresConfirmation()
                    ->modalHeading('رفض الشركة')
                    ->modalDescription('هل أنت متأكد من رفض هذه الشركة؟ لن تتمكن من نشر الوظائف.')
                    ->action(function ($record) {
                        $record->update([
                            'status'      => 'inactive',
                            'is_verified' => false,
                        ]);
                    })
                    ->successNotificationTitle('تم رفض الشركة'),

                EditAction::make()->label('تعديل'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            ->defaultSort('created_at', 'desc');
    }
}