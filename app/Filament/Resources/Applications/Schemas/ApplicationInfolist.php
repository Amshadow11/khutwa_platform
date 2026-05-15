<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Application;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('job.title')
                    ->label('الوظيفة'),

                TextEntry::make('user.id')
                    ->label('المستخدم'),

                TextEntry::make('cover_letter')
                    ->label('خطاب التقديم')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('cv_path')
                    ->label('السيرة الذاتية')
                    ->placeholder('-'),

                TextEntry::make('about')
                    ->label('نبذة')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('applicant_name')
                    ->label('اسم المتقدم')
                    ->placeholder('-'),

                TextEntry::make('applicant_email')
                    ->label('البريد الإلكتروني')
                    ->placeholder('-'),

                TextEntry::make('applicant_phone')
                    ->label('رقم الهاتف')
                    ->placeholder('-'),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge(),

                TextEntry::make('notes')
                    ->label('الملاحظات')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('status_updated_at')
                    ->label('تاريخ تحديث الحالة')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('applied_at')
                    ->label('تاريخ التقديم')
                    ->dateTime(),

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
                    ->visible(fn (Application $record): bool => $record->trashed()),
            ]);
    }
}