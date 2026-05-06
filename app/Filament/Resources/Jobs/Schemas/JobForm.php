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
                    ->relationship('company', 'id')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('requirements')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('benefits')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('category')
                    ->options(['job' => 'Job', 'training' => 'Training'])
                    ->default('job')
                    ->required(),
                TextInput::make('job_type')
                    ->default(null),
                TextInput::make('experience_level')
                    ->default(null),
                TextInput::make('location')
                    ->default(null),
                Toggle::make('remote_work')
                    ->required(),
                TextInput::make('salary')
                    ->default(null),
                TextInput::make('salary_range')
                    ->default(null),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'draft' => 'Draft'])
                    ->default('active')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('featured')
                    ->required(),
                Toggle::make('urgent')
                    ->required(),
                DatePicker::make('deadline'),
                TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('views_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('post_date')
                    ->required(),
            ]);
    }
}
