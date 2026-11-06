<?php

namespace App\Filament\Resources\LuxuryCollections\Pages;

use App\Filament\Resources\LuxuryCollections\LuxuryCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLuxuryCollection extends EditRecord
{
    protected static string $resource = LuxuryCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
