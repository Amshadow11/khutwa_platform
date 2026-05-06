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
                    ->label('Job'),
                TextEntry::make('user.id')
                    ->label('User'),
                TextEntry::make('cover_letter')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('cv_path')
                    ->placeholder('-'),
                TextEntry::make('about')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('applicant_name')
                    ->placeholder('-'),
                TextEntry::make('applicant_email')
                    ->placeholder('-'),
                TextEntry::make('applicant_phone')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status_updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('applied_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Application $record): bool => $record->trashed()),
            ]);
    }
}
