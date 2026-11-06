<?php

namespace App\Filament\Resources\LaunchAlerts\Pages;

use App\Filament\Resources\LaunchAlerts\LaunchAlertResource;
use App\Filament\Resources\LaunchAlerts\Tables\LaunchAlertsTable;
use App\Filament\Support\CsvExport;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLaunchAlerts extends ListRecords
{
    protected static string $resource = LaunchAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Every row matching the table's current search and filters
            CsvExport::action('launch-alerts', LaunchAlertsTable::exportColumns()),
            CreateAction::make(),
        ];
    }
}
