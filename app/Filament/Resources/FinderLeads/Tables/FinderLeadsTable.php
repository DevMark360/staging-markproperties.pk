<?php

namespace App\Filament\Resources\FinderLeads\Tables;

use App\Filament\Support\CsvExport;
use App\Filament\Support\DateRangeFilter;
use App\Models\FinderLead;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinderLeadsTable
{
    /** Columns written to the CSV export */
    public static function exportColumns(): array
    {
        return [
            'Date' => fn (FinderLead $r) => $r->created_at?->format('Y-m-d H:i'),
            'Name' => fn (FinderLead $r) => $r->name,
            'Phone' => fn (FinderLead $r) => $r->phone,
            'Email' => fn (FinderLead $r) => $r->email,
            'Answers' => fn (FinderLead $r) => collect($r->answerLines())->map(fn ($a, $q) => "{$q}: {$a}")->implode(' | '),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('j M Y, g:i a')
                    ->sortable(),
                TextColumn::make('name')
                    ->weight('medium')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Number')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('answers')
                    ->label('Answers')
                    ->state(fn (FinderLead $record) => $record->answersSummary())
                    ->limit(70)
                    ->tooltip(fn (FinderLead $record) => $record->answersSummary())
                    ->placeholder('—'),
            ])
            ->filters([
                DateRangeFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CsvExport::bulkAction('property-finder-leads', self::exportColumns()),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No property finder leads yet')
            ->emptyStateDescription('Visitors who answer the home page pop-up and leave their number will appear here.');
    }
}
