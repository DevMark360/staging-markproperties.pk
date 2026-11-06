<?php

namespace App\Filament\Resources\Areas\Tables;

use App\Filament\Support\InUseGuard;
use App\Filament\Support\ListFields;
use App\Models\Area;
use App\Models\City;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('projects'))
            // Drag order is the home page order; filter "On home page" first to arrange just those
            ->reorderable('home_order')
            ->defaultSort(fn ($query) => $query->orderByDesc('show_on_home')->orderBy('home_order')->orderBy('name'))
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('site')
                    ->imageWidth(48)
                    ->imageHeight(56)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),
                TextColumn::make('name')
                    ->weight('medium')
                    ->description(fn (Area $record) => City::nameFor($record->city_slug))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')->label('Web address')->prefix('/projects?area=')->color('gray')->toggleable(),
                ToggleColumn::make('show_on_home')
                    ->label('Home page')
                    // Without a photo and description the home card would be empty
                    ->disabled(fn (Area $record) => ! $record->show_on_home && (! $record->image || ! $record->description))
                    ->tooltip(fn (Area $record) => ! $record->image || ! $record->description ? 'Add a photo and description first' : null),
                ListFields::projectsColumn(),
            ])
            ->filters([
                SelectFilter::make('city_slug')
                    ->label('City')
                    ->options(fn () => City::options()),
                TernaryFilter::make('show_on_home')
                    ->label('On home page'),
            ])
            ->recordActions([
                EditAction::make(),
                InUseGuard::deleteAction(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([InUseGuard::bulkDeleteAction()]),
            ])
            ->emptyStateHeading('No areas yet')
            ->emptyStateDescription('Add an area, or run "php artisan catalogue:import" to copy the website lists here.');
    }
}
