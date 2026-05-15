<?php

namespace App\Filament\Resources\UpgradeRequests;

use App\Filament\Resources\UpgradeRequests\Pages\ListUpgradeRequests;
use App\Filament\Resources\UpgradeRequests\Pages\ViewUpgradeRequest;
use App\Filament\Resources\UpgradeRequests\Schemas\UpgradeRequestInfolist;
use App\Filament\Resources\UpgradeRequests\Tables\UpgradeRequestsTable;
use App\Models\SubscriptionUpgradeRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UpgradeRequestResource extends Resource
{
    protected static ?string $model = SubscriptionUpgradeRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static ?string $navigationLabel = 'طلبات الترقية';

    protected static ?string $modelLabel = 'طلب ترقية';

    protected static ?string $pluralModelLabel = 'طلبات الترقية';

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة الاشتراكات';

    protected static ?int $navigationSort = 2;

    /**
     * عداد الطلبات المعلّقة في القائمة الجانبية.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = SubscriptionUpgradeRequest::pending()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return UpgradeRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UpgradeRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUpgradeRequests::route('/'),
            'view'  => ViewUpgradeRequest::route('/{record}'),
        ];
    }
}