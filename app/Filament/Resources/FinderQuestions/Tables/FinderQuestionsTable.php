<?php

namespace App\Filament\Resources\FinderQuestions\Tables;

use App\Models\FinderQuestion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class FinderQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Question')
                    ->weight('medium')
                    ->wrap()
                    ->description(fn (FinderQuestion $record) => $record->hint),
                TextColumn::make('source')
                    ->label('Options')
                    ->formatStateUsing(fn (FinderQuestion $record) => $record->source === 'custom'
                        ? count($record->options ?? []).' of my own'
                        : $record->sourceLabel())
                    ->badge()
                    ->color(fn (string $state) => $state === 'custom' ? 'gray' : 'primary'),
                IconColumn::make('multiple')->label('More than one')->boolean(),
                ToggleColumn::make('is_active')->label('Asked'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('No questions yet')
            ->emptyStateDescription('The property finder pop-up stays hidden until there is at least one question.');
    }
}
