<?php

namespace App\Filament\Resources\Stages\Tables;

use App\Filament\Support\InUseGuard;
use App\Filament\Support\ListFields;
use App\Models\Stage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class StagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('projects'))
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->weight('medium')->searchable()
                    ->description(fn (Stage $record) => $record->description)->wrap(),
                TextColumn::make('slug')->label('Web address')->prefix('/projects?status=')->color('gray'),
                ToggleColumn::make('show_in_menu')->label('In menu'),
                ListFields::projectsColumn(),
            ])
            ->recordActions([
                EditAction::make(),
                InUseGuard::deleteAction(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([InUseGuard::bulkDeleteAction()]),
            ])
            ->emptyStateHeading('No stages yet')
            ->emptyStateDescription('Add a stage, or run "php artisan catalogue:import" to copy the website lists here.');
    }
}
