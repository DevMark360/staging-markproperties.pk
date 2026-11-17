<?php

namespace App\Filament\Resources\FinderLeads\Pages;

use App\Filament\Resources\FinderLeads\FinderLeadResource;
use App\Filament\Resources\FinderLeads\Tables\FinderLeadsTable;
use App\Filament\Support\CsvExport;
use Filament\Resources\Pages\ListRecords;

class ListFinderLeads extends ListRecords
{
    protected static string $resource = FinderLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Every row matching the table's current search and filters
            CsvExport::action('property-finder-leads', FinderLeadsTable::exportColumns()),
        ];
    }
}
