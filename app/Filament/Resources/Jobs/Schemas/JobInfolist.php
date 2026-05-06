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
                    ->label('Company'),
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('requirements')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('benefits')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('category')
                    ->badge(),
                TextEntry::make('job_type')
                    ->placeholder('-'),
                TextEntry::make('experience_level')
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->placeholder('-'),
                IconEntry::make('remote_work')
                    ->boolean(),
                TextEntry::make('salary')
                    ->placeholder('-'),
                TextEntry::make('salary_range')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('featured')
                    ->boolean(),
                IconEntry::make('urgent')
                    ->boolean(),
                TextEntry::make('deadline')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('views')
                    ->numeric(),
                TextEntry::make('views_count')
                    ->numeric(),
                TextEntry::make('post_date')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Job $record): bool => $record->trashed()),
            ]);
    }
}
