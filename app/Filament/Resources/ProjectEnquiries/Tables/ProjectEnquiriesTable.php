<?php

namespace App\Filament\Resources\ProjectEnquiries\Tables;

use App\Filament\Support\CsvExport;
use App\Filament\Support\DateRangeFilter;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\ProjectEnquiry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectEnquiriesTable
{
    /** Columns written to the CSV export */
    public static function exportColumns(): array
    {
        return [
            'Date' => fn (ProjectEnquiry $r) => $r->created_at?->format('Y-m-d H:i'),
            'Project' => fn (ProjectEnquiry $r) => $r->project_name,
            'Name' => fn (ProjectEnquiry $r) => $r->name,
            'Phone' => fn (ProjectEnquiry $r) => $r->phone,
            'Email' => fn (ProjectEnquiry $r) => $r->email,
            'Interested in' => fn (ProjectEnquiry $r) => $r->interestLabel(),
            'Message' => fn (ProjectEnquiry $r) => $r->message,
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
                TextColumn::make('project_name')
                    ->label('Project')
                    ->url(fn (ProjectEnquiry $record) => route('projects.show', $record->project_slug), shouldOpenInNewTab: true)
                    ->weight('medium')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
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
                TextColumn::make('interest')
                    ->label('Interested in')
                    ->formatStateUsing(fn (ProjectEnquiry $record) => $record->interestLabel())
                    ->badge()
                    ->color('gray'),
                TextColumn::make('message')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_slug')
                    ->label('Project')
                    ->options(fn () => ProjectEnquiry::query()->distinct()->orderBy('project_name')->pluck('project_name', 'project_slug')->all())
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('interest')
                    ->label('Interested in')
                    ->options(StoreEnquiryRequest::INTERESTS),
                DateRangeFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CsvExport::bulkAction('project-enquiries', self::exportColumns()),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No project enquiries yet')
            ->emptyStateDescription('Enquiries sent from project pages will appear here.');
    }
}
