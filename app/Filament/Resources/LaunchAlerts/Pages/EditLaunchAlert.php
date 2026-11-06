<?php

namespace App\Filament\Resources\LaunchAlerts\Pages;

use App\Filament\Resources\LaunchAlerts\LaunchAlertResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLaunchAlert extends EditRecord
{
    protected static string $resource = LaunchAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
