<?php

namespace App\Filament\Resources\Amenities\Pages;

use App\Filament\Resources\Amenities\AmenityResource;
use App\Filament\Support\FeatureList;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAmenity extends EditRecord
{
    protected static string $resource = AmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            FeatureList::deleteAction(DeleteAction::make()),
        ];
    }
}
