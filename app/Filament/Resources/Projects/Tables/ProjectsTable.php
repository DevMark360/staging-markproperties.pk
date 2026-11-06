<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Area;
use App\Models\City;
use App\Models\Developer;
use App\Models\Project;
use App\Models\PropertyType;
use App\Models\Stage;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('site')
                    ->imageWidth(64)
                    ->imageHeight(48)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),
                TextColumn::make('name')
                    ->weight('medium')
                    ->description(fn (Project $record) => $record->area.', '.$record->cityName())
                    ->searchable(['name', 'area', 'slug'])
                    ->sortable(),
                TextColumn::make('developer_slug')
                    ->label('Builder')
                    ->formatStateUsing(fn (Project $record) => $record->developerName() ?? $record->developer_slug)
                    ->sortable(),
                TextColumn::make('stage_slug')
                    ->label('Stage')
                    ->formatStateUsing(fn (?string $state) => Stage::nameFor($state))
                    ->badge()
                    ->color(fn (?string $state) => $state === 'pre-launch' ? 'primary' : 'gray')
                    ->placeholder('—'),
                TextColumn::make('type_slug')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state) => PropertyType::nameFor($state))
                    ->placeholder('—')
                    ->toggleable(),
                ToggleColumn::make('is_published')
                    ->label('Published'),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('developer_slug')
                    ->label('Builder')
                    ->options(fn () => Developer::options())
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('city_slug')
                    ->label('City')
                    ->options(fn () => City::options())
                    ->multiple(),
                SelectFilter::make('area_slug')
                    ->label('Area')
                    ->options(fn () => Area::optionsFor(null))
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('stage_slug')
                    ->label('Stage')
                    ->options(fn () => Stage::options()),
                SelectFilter::make('type_slug')
                    ->label('Property type')
                    ->options(fn () => PropertyType::options()),
                TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('hide')
                        ->label('Hide selected')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No projects yet')
            ->emptyStateDescription('Add a project, or run "php artisan projects:import" to copy the website projects here.');
    }
}
