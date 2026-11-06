<?php

namespace App\Filament\Resources\ProjectEnquiries\Pages;

use App\Filament\Resources\ProjectEnquiries\ProjectEnquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProjectEnquiry extends EditRecord
{
    protected static string $resource = ProjectEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
