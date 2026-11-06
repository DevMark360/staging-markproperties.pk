<?php

namespace App\Filament\Resources\Developers\Tables;

use App\Models\Developer;
use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DevelopersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->addSelect([
                'projects_count' => Project::selectRaw('count(*)')->whereColumn('developer_slug', 'developers.slug'),
            ]))
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->disk('site')
                    ->imageWidth(56)
                    ->imageHeight(40)
                    ->extraImgAttributes(['class' => 'object-contain']),
                TextColumn::make('name')
                    ->weight('medium')
                    ->searchable(['name', 'slug'])
                    ->sortable(),
                ToggleColumn::make('is_partner')
                    ->label('Partner'),
                TextColumn::make('projects_count')
                    ->label('Projects')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_partner')->label('Partner'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                self::deleteAction(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            [$blocked, $free] = $records->partition(fn (Developer $d) => $d->projectCount() > 0);
                            $free->each->delete();

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->title('Not deleted: '.$blocked->pluck('name')->join(', '))
                                    ->body('They still have projects. Move or delete their projects first.')
                                    ->warning()
                                    ->send();
                            }
                            if ($free->isNotEmpty()) {
                                Notification::make()->title('Deleted '.$free->pluck('name')->join(', '))->success()->send();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('No developers yet')
            ->emptyStateDescription('Add a developer, or run "php artisan developers:import" to copy the website developers here.');
    }

    /** Refuses to delete a developer that still has projects (used on the table row and the edit page). */
    public static function deleteAction(DeleteAction $action): DeleteAction
    {
        return $action->before(function (DeleteAction $action, Developer $record) {
            if ($count = $record->projectCount()) {
                Notification::make()
                    ->title($record->name.' still has '.$count.' '.str('project')->plural($count).'.')
                    ->body('Move or delete those projects first.')
                    ->danger()
                    ->send();
                $action->cancel();
            }
        });
    }
}
