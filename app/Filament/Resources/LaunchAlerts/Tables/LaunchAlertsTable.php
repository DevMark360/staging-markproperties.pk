<?php

namespace App\Filament\Resources\LaunchAlerts\Tables;

use App\Filament\Support\CsvExport;
use App\Filament\Support\DateRangeFilter;
use App\Models\City;
use App\Models\LaunchAlert;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LaunchAlertsTable
{
    /** Columns written to the CSV export */
    public static function exportColumns(): array
    {
        return [
            'Signed up' => fn (LaunchAlert $r) => $r->created_at?->format('Y-m-d H:i'),
            'Last updated' => fn (LaunchAlert $r) => $r->updated_at?->format('Y-m-d H:i'),
            'Email' => fn (LaunchAlert $r) => $r->email,
            'Phone' => fn (LaunchAlert $r) => $r->phone,
            'City' => fn (LaunchAlert $r) => $r->cityLabel(),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Signed up')
                    ->dateTime('j M Y, g:i a')
                    ->sortable(),
                TextColumn::make('email')
                    ->weight('medium')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label('Number')
                    // The digits-only match is added only when the search has digits: otherwise it was
                    // LIKE '%%', which matched every row (A2)
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('phone', 'like', "%{$search}%")
                        ->when(preg_replace('/\D/', '', $search), fn ($q, string $digits) => $q->orWhere('phone_key', 'like', "%{$digits}%")))
                    ->copyable(),
                TextColumn::make('city')
                    ->formatStateUsing(fn (LaunchAlert $record) => $record->cityLabel())
                    ->placeholder('Any city')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->dateTime('j M Y, g:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('city')
                    ->options(fn () => City::options())
                    ->searchable()
                    ->multiple(),
                DateRangeFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CsvExport::bulkAction('launch-alerts', self::exportColumns()),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No launch-alert subscribers yet')
            ->emptyStateDescription('People who sign up for launch alerts on the home page will appear here.');
    }
}
