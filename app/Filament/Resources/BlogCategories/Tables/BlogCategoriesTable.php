<?php

namespace App\Filament\Resources\BlogCategories\Tables;

use App\Models\BlogCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class BlogCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('posts'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->weight('medium')->searchable()->sortable(),
                TextColumn::make('slug')->label('Web address')->prefix('/blog?category=')->color('gray'),
                TextColumn::make('posts_count')->label('Posts')->sortable()->alignEnd(),
            ])
            ->recordActions([
                EditAction::make(),
                self::deleteAction(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->action(function (Collection $records) {
                        [$blocked, $free] = $records->partition(fn (BlogCategory $c) => $c->posts()->exists());
                        $free->each->delete();
                        if ($blocked->isNotEmpty()) {
                            Notification::make()->title('Not deleted: '.$blocked->pluck('name')->join(', '))
                                ->body('These categories still have posts. Move the posts to another category first.')->warning()->send();
                        }
                    }),
                ]),
            ])
            ->emptyStateHeading('No categories yet');
    }

    /** Refuses to delete a category that still has posts. */
    public static function deleteAction(DeleteAction $action): DeleteAction
    {
        return $action->before(function (DeleteAction $action, BlogCategory $record) {
            if ($count = $record->posts()->count()) {
                Notification::make()
                    ->title('“'.$record->name.'” still has '.$count.' '.str('post')->plural($count).'.')
                    ->body('Move those posts to another category first.')
                    ->danger()
                    ->send();
                $action->cancel();
            }
        });
    }
}
