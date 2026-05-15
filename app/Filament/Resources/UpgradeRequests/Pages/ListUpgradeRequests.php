<?php

namespace App\Filament\Resources\UpgradeRequests\Pages;

use App\Filament\Resources\UpgradeRequests\UpgradeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListUpgradeRequests extends ListRecords
{
    protected static string $resource = UpgradeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
        // طلبات الترقية تُنشأ من الشركات فقط — لا create من Admin
    }
}