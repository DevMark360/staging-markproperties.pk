<?php

namespace App\Filament\Resources\FinderLeads\Pages;

use App\Filament\Resources\FinderLeads\FinderLeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFinderLead extends ViewRecord
{
    protected static string $resource = FinderLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
