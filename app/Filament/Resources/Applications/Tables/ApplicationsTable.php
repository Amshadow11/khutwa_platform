<?php

namespace App\Filament\Resources\Applications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('اسم المتقدم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('applicant_email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),

                TextColumn::make('applicant_phone')
                    ->label('الهاتف')
                    ->searchable(),

                TextColumn::make('job.title')
                    ->label('الوظيفة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('job.company.company_name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'pending'     => 'warning',
                        'viewed'      => 'info',
                        'shortlisted' => 'primary',
                        'interview'   => 'purple',
                        'accepted'    => 'success',
                        'rejected'    => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'pending'     => 'قيد المراجعة',
                        'viewed'      => 'تمت المشاهدة',
                        'shortlisted' => 'في القائمة المختصرة',
                        'interview'   => 'دُعي للمقابلة',
                        'accepted'    => 'مقبول',
                        'rejected'    => 'مرفوض',
                        default       => $state,
                    }),

                TextColumn::make('applied_at')
                    ->label('تاريخ التقديم')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status_updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'     => 'قيد المراجعة',
                        'viewed'      => 'تمت المشاهدة',
                        'shortlisted' => 'في القائمة المختصرة',
                        'interview'   => 'دُعي للمقابلة',
                        'accepted'    => 'مقبول',
                        'rejected'    => 'مرفوض',
                    ]),

                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make()->label('عرض'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            ->defaultSort('applied_at', 'desc');
    }
}