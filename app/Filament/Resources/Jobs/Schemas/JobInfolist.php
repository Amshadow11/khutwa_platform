<?php

namespace App\Filament\Resources\Jobs\Schemas;

use App\Models\Job;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company.id')
                    ->label('الشركة'),

                TextEntry::make('title')
                    ->label('عنوان الوظيفة'),

                TextEntry::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),

                TextEntry::make('requirements')
                    ->label('المتطلبات')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('benefits')
                    ->label('المميزات')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('category')
                    ->label('التصنيف')
                    ->badge(),

                TextEntry::make('job_type')
                    ->label('نوع الوظيفة')
                    ->placeholder('-'),

                TextEntry::make('experience_level')
                    ->label('مستوى الخبرة')
                    ->placeholder('-'),

                TextEntry::make('location')
                    ->label('الموقع')
                    ->placeholder('-'),

                IconEntry::make('remote_work')
                    ->label('عمل عن بُعد')
                    ->boolean(),

                TextEntry::make('salary')
                    ->label('الراتب')
                    ->placeholder('-'),

                TextEntry::make('salary_range')
                    ->label('نطاق الراتب')
                    ->placeholder('-'),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge(),

                IconEntry::make('is_active')
                    ->label('مفعلة')
                    ->boolean(),

                IconEntry::make('featured')
                    ->label('مميزة')
                    ->boolean(),

                IconEntry::make('urgent')
                    ->label('عاجلة')
                    ->boolean(),

                TextEntry::make('deadline')
                    ->label('آخر موعد')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('views')
                    ->label('عدد المشاهدات')
                    ->numeric(),

                TextEntry::make('views_count')
                    ->label('إجمالي المشاهدات')
                    ->numeric(),

                TextEntry::make('post_date')
                    ->label('تاريخ النشر')
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
                    ->visible(fn (Job $record): bool => $record->trashed()),
            ]);
    }
}