<?php

namespace App\Filament\Resources\ProjectEnquiries\Pages;

use App\Filament\Resources\ProjectEnquiries\ProjectEnquiryResource;
use App\Filament\Resources\ProjectEnquiries\Tables\ProjectEnquiriesTable;
use App\Filament\Support\CsvExport;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectEnquiries extends ListRecords
{
    protected static string $resource = ProjectEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Every row matching the table's current search and filters
            CsvExport::action('project-enquiries', ProjectEnquiriesTable::exportColumns()),
            CreateAction::make(),
        ];
    }
}
