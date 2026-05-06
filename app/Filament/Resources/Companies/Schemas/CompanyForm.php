<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('phone_code')
                    ->tel()
                    ->default('YE'),
                TextInput::make('logo')
                    ->default(null),
                TextInput::make('profile_picture')
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('address')
                    ->default(null),
                TextInput::make('website')
                    ->url()
                    ->default(null),
                TextInput::make('industry')
                    ->default(null),
                TextInput::make('founded_year')
                    ->default(null),
                Select::make('company_size')
                    ->options(['startup' => 'Startup', 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'])
                    ->default('small')
                    ->required(),
                TextInput::make('subscription_plan')
                    ->required()
                    ->default('free'),
                Toggle::make('subscription')
                    ->required(),
                DatePicker::make('subscription_started'),
                DatePicker::make('subscription_end'),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'pending' => 'Pending'])
                    ->default('pending')
                    ->required(),
                Toggle::make('is_verified')
                    ->required(),
                TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('role')
                    ->required()
                    ->default('company'),
                DateTimePicker::make('last_login'),
            ]);
    }
}
