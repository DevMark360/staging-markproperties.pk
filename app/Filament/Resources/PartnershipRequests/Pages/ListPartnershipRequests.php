<?php

namespace App\Filament\Resources\PartnershipRequests\Pages;

use App\Filament\Resources\PartnershipRequests\PartnershipRequestResource;
use App\Filament\Resources\PartnershipRequests\Tables\PartnershipRequestsTable;
use App\Filament\Support\CsvExport;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnershipRequests extends ListRecords
{
    protected static string $resource = PartnershipRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Every row matching the table's current search and filters
            CsvExport::action('partnership-requests', PartnershipRequestsTable::exportColumns()),
            CreateAction::make(),
        ];
    }
}
