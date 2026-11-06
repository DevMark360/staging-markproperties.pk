<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Filament\Support\CsvExport;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Every row matching the table's current search and filters
            CsvExport::action('contact-messages', ContactMessagesTable::exportColumns()),
            CreateAction::make(),
        ];
    }
}
