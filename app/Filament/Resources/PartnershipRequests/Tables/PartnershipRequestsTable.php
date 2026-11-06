<?php

namespace App\Filament\Resources\PartnershipRequests\Tables;

use App\Filament\Support\CsvExport;
use App\Filament\Support\DateRangeFilter;
use App\Models\PartnershipRequest;
use App\Models\PropertyType;
use App\Models\Stage;
use App\Support\PakistanCities;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PartnershipRequestsTable
{
    /** Columns written to the CSV export */
    public static function exportColumns(): array
    {
        return [
            'Date' => fn (PartnershipRequest $r) => $r->created_at?->format('Y-m-d H:i'),
            'Company' => fn (PartnershipRequest $r) => $r->company,
            'Project' => fn (PartnershipRequest $r) => $r->project,
            'Stage' => fn (PartnershipRequest $r) => $r->stageLabel(),
            'Type' => fn (PartnershipRequest $r) => $r->typeLabel(),
            'City' => fn (PartnershipRequest $r) => $r->cityLabel(),
            'Contact name' => fn (PartnershipRequest $r) => $r->name,
            'Phone' => fn (PartnershipRequest $r) => $r->phone,
            'Email' => fn (PartnershipRequest $r) => $r->email,
            'Message' => fn (PartnershipRequest $r) => $r->message,
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
                TextColumn::make('company')
                    ->weight('medium')
                    ->description(fn (PartnershipRequest $record) => $record->project)
                    ->searchable(['company', 'project'])
                    ->sortable(),
                TextColumn::make('stage')
                    ->formatStateUsing(fn (PartnershipRequest $record) => $record->stageLabel())
                    ->badge()
                    ->color(fn (string $state) => $state === 'pre-launch' ? 'primary' : 'gray'),
                TextColumn::make('type')
                    ->formatStateUsing(fn (PartnershipRequest $record) => $record->typeLabel())
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')
                    ->formatStateUsing(fn (PartnershipRequest $record) => $record->cityLabel())
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Number')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('message')
                    ->limit(60)
                    ->tooltip(fn (PartnershipRequest $record) => $record->message)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->options(fn () => Stage::options())
                    ->multiple(),
                SelectFilter::make('type')
                    ->label('Project type')
                    ->options(fn () => PropertyType::options())
                    ->multiple(),
                SelectFilter::make('city')
                    ->options(fn () => PakistanCities::byProvince())
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
                    CsvExport::bulkAction('partnership-requests', self::exportColumns()),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No partnership requests yet')
            ->emptyStateDescription('Requests from builders on the Partner With Us page will appear here.');
    }
}
