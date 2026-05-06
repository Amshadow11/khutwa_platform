<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('الاسم الكامل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->label('اسم المستخدم')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),

                IconColumn::make('email_verified_at')
                    ->label('بريد مفعّل')
                    ->boolean()
                    ->getStateUsing(fn($record) => ! is_null($record->email_verified_at)),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        default    => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextColumn::make('last_login')
                    ->label('آخر دخول')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->date()
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'   => 'نشط',
                        'inactive' => 'معطّل',
                    ]),

                SelectFilter::make('is_active')
                    ->label('التفعيل')
                    ->options([
                        '1' => 'مفعّل',
                        '0' => 'معطّل',
                    ]),

                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make()->label('عرض'),

                // ✅ تفعيل المستخدم
                Action::make('activate')
                    ->label('تفعيل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => ! $record->is_active || $record->status !== 'active')
                    ->requiresConfirmation()
                    ->modalHeading('تفعيل المستخدم')
                    ->modalDescription('هل أنت متأكد من تفعيل هذا المستخدم؟')
                    ->action(function ($record) {
                        $record->update([
                            'status'    => 'active',
                            'is_active' => true,
                        ]);
                    })
                    ->successNotificationTitle('تم تفعيل المستخدم'),

                // ❌ تعطيل المستخدم
                Action::make('deactivate')
                    ->label('تعطيل')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->is_active || $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('تعطيل المستخدم')
                    ->modalDescription('هل أنت متأكد من تعطيل هذا المستخدم؟ لن يتمكن من تسجيل الدخول.')
                    ->action(function ($record) {
                        $record->update([
                            'status'    => 'inactive',
                            'is_active' => false,
                        ]);
                    })
                    ->successNotificationTitle('تم تعطيل المستخدم'),

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