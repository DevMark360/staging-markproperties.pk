<?php

namespace App\Filament\Resources\LuxuryCollections\Pages;

use App\Filament\Resources\LuxuryCollections\LuxuryCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLuxuryCollections extends ListRecords
{
    protected static string $resource = LuxuryCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
