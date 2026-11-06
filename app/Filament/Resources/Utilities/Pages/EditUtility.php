<?php

namespace App\Filament\Resources\Utilities\Pages;

use App\Filament\Resources\Utilities\UtilityResource;
use App\Filament\Support\FeatureList;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUtility extends EditRecord
{
    protected static string $resource = UtilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            FeatureList::deleteAction(DeleteAction::make()),
        ];
    }
}
