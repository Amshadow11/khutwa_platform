<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('username'),
                TextEntry::make('full_name')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('phone_code')
                    ->placeholder('-'),
                TextEntry::make('profile_picture')
                    ->placeholder('-'),
                ImageEntry::make('profile_image')
                    ->placeholder('-'),
                TextEntry::make('bio')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('birth_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('linkedin_url')
                    ->placeholder('-'),
                TextEntry::make('github_url')
                    ->placeholder('-'),
                TextEntry::make('portfolio_url')
                    ->placeholder('-'),
                TextEntry::make('skills')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('experience')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('education')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('email_verified')
                    ->boolean(),
                IconEntry::make('phone_verified')
                    ->boolean(),
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
                    ->visible(fn (User $record): bool => $record->trashed()),
            ]);
    }
}
