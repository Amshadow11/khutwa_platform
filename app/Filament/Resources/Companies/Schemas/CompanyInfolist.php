<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\Company;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company_name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('phone_code')
                    ->placeholder('-'),
                TextEntry::make('logo')
                    ->placeholder('-'),
                TextEntry::make('profile_picture')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('website')
                    ->placeholder('-'),
                TextEntry::make('industry')
                    ->placeholder('-'),
                TextEntry::make('founded_year')
                    ->placeholder('-'),
                TextEntry::make('company_size')
                    ->badge(),
                TextEntry::make('subscription_plan'),
                IconEntry::make('subscription')
                    ->boolean(),
                TextEntry::make('subscription_started')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('subscription_end')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                IconEntry::make('is_verified')
                    ->boolean(),
                TextEntry::make('views')
                    ->numeric(),
                TextEntry::make('role'),
                TextEntry::make('last_login')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Company $record): bool => $record->trashed()),
            ]);
    }
}
