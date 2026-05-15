<?php

namespace App\Filament\Resources\Jobs\Tables;

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

class JobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان الوظيفة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.company_name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'job'      => 'وظيفة',
                        'training' => 'تدريب',
                        default    => $state,
                    })
                    ->color(fn(string $state) => match($state) {
                        'job'      => 'success',
                        'training' => 'info',
                        default    => 'gray',
                    }),

                TextColumn::make('job_type')
                    ->label('نوع الدوام')
                    ->badge(),

                TextColumn::make('location')
                    ->label('الموقع')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active'   => 'نشطة',
                        'inactive' => 'معطّلة',
                        'expired'  => 'منتهية',
                        'draft'    => 'مسودة',
                        default    => $state,
                    })
                    ->color(fn(string $state) => match($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        'expired'  => 'warning',
                        'draft'    => 'gray',
                        default    => 'gray',
                    }),

                IconColumn::make('featured')
                    ->label('مميزة')
                    ->boolean(),

                IconColumn::make('urgent')
                    ->label('عاجلة')
                    ->boolean(),

                TextColumn::make('views')
                    ->label('عدد المشاهدات')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('deadline')
                    ->label('آخر موعد')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ النشر')
                    ->date()
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'   => 'نشطة',
                        'inactive' => 'معطّلة',
                        'expired'  => 'منتهية',
                        'draft'    => 'مسودة',
                    ]),

                SelectFilter::make('category')
                    ->label('النوع')
                    ->options([
                        'job'      => 'وظيفة',
                        'training' => 'تدريب',
                    ]),

                SelectFilter::make('featured')
                    ->label('التمييز')
                    ->options([
                        '1' => 'مميزة',
                        '0' => 'عادية',
                    ]),

                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])

            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),

                Action::make('activate')
                    ->label('تفعيل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->modalHeading('تفعيل الوظيفة')
                    ->modalDescription('هل أنت متأكد من تفعيل هذه الوظيفة؟')
                    ->action(function ($record) {
                        $record->update([
                            'status'    => 'active',
                            'is_active' => true,
                        ]);
                    })
                    ->successNotificationTitle('تم تفعيل الوظيفة'),

                Action::make('deactivate')
                    ->label('إخفاء')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('إخفاء الوظيفة')
                    ->modalDescription('هل أنت متأكد من إخفاء هذه الوظيفة؟ لن تظهر للباحثين.')
                    ->action(function ($record) {
                        $record->update([
                            'status'    => 'inactive',
                            'is_active' => false,
                        ]);
                    })
                    ->successNotificationTitle('تم إخفاء الوظيفة'),

                Action::make('feature')
                    ->label('تمييز')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn($record) => ! $record->featured)
                    ->action(function ($record) {
                        $record->update(['featured' => true]);
                    })
                    ->successNotificationTitle('تم تمييز الوظيفة'),

                Action::make('unfeature')
                    ->label('إلغاء التمييز')
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn($record) => $record->featured)
                    ->action(function ($record) {
                        $record->update(['featured' => false]);
                    })
                    ->successNotificationTitle('تم إلغاء تمييز الوظيفة'),

                EditAction::make()
                    ->label('تعديل'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف'),

                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي'),

                    RestoreBulkAction::make()
                        ->label('استعادة'),
                ]),
            ])

            ->defaultSort('created_at', 'desc');
    }
}