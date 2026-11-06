<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /** Place the project in (or take it out of) the home slider and Trending, replacing the chosen project if a section was full. */
    protected function afterSave(): void
    {
        // Tell the admin which project made room, if a full section was replaced into (A9)
        foreach ($this->record->syncSections([
            'home' => $this->data['replace_home'] ?? null,
            'trending' => $this->data['replace_trending'] ?? null,
        ]) as $section => $name) {
            Notification::make()->title($name.' was removed from the '.$section)->body('The project itself is not changed.')->info()->send();
        }
    }
}
