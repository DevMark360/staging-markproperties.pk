<?php

namespace App\Filament\Support;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/** Delete actions for list items (cities, areas, types, stages) that refuse while projects still use the item. */
class InUseGuard
{
    public static function deleteAction(DeleteAction $action): DeleteAction
    {
        return $action->before(function (DeleteAction $action, Model $record) {
            if ($usage = $record->usage()) {
                Notification::make()
                    ->title('“'.$record->name.'” is still used by '.$usage.'.')
                    ->body('Move them to another one first, then delete it.')
                    ->danger()
                    ->send();
                $action->cancel();
            }
        });
    }

    public static function bulkDeleteAction(): DeleteBulkAction
    {
        return DeleteBulkAction::make()->action(function (Collection $records) {
            [$blocked, $free] = $records->partition(fn (Model $record) => $record->usage() !== null);
            $free->each->delete();
            if ($blocked->isNotEmpty()) {
                Notification::make()->title('Not deleted: '.$blocked->pluck('name')->join(', '))
                    ->body('These are still used by projects. Move the projects first.')->warning()->send();
            }
        });
    }
}
