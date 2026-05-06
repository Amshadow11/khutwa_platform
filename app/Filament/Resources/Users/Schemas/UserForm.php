<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->required(),
                TextInput::make('full_name')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('phone_code')
                    ->tel()
                    ->default('YE'),
                TextInput::make('profile_picture')
                    ->default(null),
                FileUpload::make('profile_image')
                    ->image(),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('address')
                    ->default(null),
                DatePicker::make('birth_date'),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female'])
                    ->default(null),
                TextInput::make('linkedin_url')
                    ->url()
                    ->default(null),
                TextInput::make('github_url')
                    ->url()
                    ->default(null),
                TextInput::make('portfolio_url')
                    ->url()
                    ->default(null),
                Textarea::make('skills')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('experience')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('education')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                    ->default('active')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('email_verified')
                    ->required(),
                Toggle::make('phone_verified')
                    ->required(),
                TextInput::make('role')
                    ->required()
                    ->default('job_seeker'),
                DateTimePicker::make('last_login'),
            ]);
    }
}
