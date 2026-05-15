<?php

namespace App\Filament\Resources\Jobs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class JobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'id')
                    ->required(),

                TextInput::make('title')
                    ->label('عنوان الوظيفة')
                    ->required(),

                Textarea::make('description')
                    ->label('الوصف')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('requirements')
                    ->label('المتطلبات')
                    ->default(null)
                    ->columnSpanFull(),

                Textarea::make('benefits')
                    ->label('المميزات')
                    ->default(null)
                    ->columnSpanFull(),

                Select::make('category')
                    ->label('التصنيف')
                    ->options([
                        'job'      => 'وظيفة',
                        'training' => 'تدريب',
                    ])
                    ->default('job')
                    ->required(),

                TextInput::make('job_type')
                    ->label('نوع الوظيفة')
                    ->default(null),

                TextInput::make('experience_level')
                    ->label('مستوى الخبرة')
                    ->default(null),

                TextInput::make('location')
                    ->label('الموقع')
                    ->default(null),

                Toggle::make('remote_work')
                    ->label('عمل عن بُعد')
                    ->required(),

                TextInput::make('salary')
                    ->label('الراتب')
                    ->default(null),

                TextInput::make('salary_range')
                    ->label('نطاق الراتب')
                    ->default(null),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'   => 'نشطة',
                        'inactive' => 'غير نشطة',
                        'expired'  => 'منتهية',
                        'draft'    => 'مسودة',
                    ])
                    ->default('active')
                    ->required(),

                Toggle::make('is_active')
                    ->label('مفعلة')
                    ->required(),

                Toggle::make('featured')
                    ->label('مميزة')
                    ->required(),

                Toggle::make('urgent')
                    ->label('عاجلة')
                    ->required(),

                DatePicker::make('deadline')
                    ->label('آخر موعد'),

                TextInput::make('views')
                    ->label('عدد المشاهدات')
                    ->required()
                    ->numeric()
                    ->default(0),

                TextInput::make('views_count')
                    ->label('إجمالي المشاهدات')
                    ->required()
                    ->numeric()
                    ->default(0),

                DateTimePicker::make('post_date')
                    ->label('تاريخ النشر')
                    ->required(),
            ]);
    }
}