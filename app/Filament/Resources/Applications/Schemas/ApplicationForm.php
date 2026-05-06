<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('job_id')
                    ->relationship('job', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Textarea::make('cover_letter')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('cv_path')
                    ->default(null),
                Textarea::make('about')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('applicant_name')
                    ->default(null),
                TextInput::make('applicant_email')
                    ->email()
                    ->default(null),
                TextInput::make('applicant_phone')
                    ->tel()
                    ->default(null),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'viewed' => 'Viewed',
            'shortlisted' => 'Shortlisted',
            'interview' => 'Interview',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('status_updated_at'),
                DateTimePicker::make('applied_at')
                    ->required(),
            ]);
    }
}
