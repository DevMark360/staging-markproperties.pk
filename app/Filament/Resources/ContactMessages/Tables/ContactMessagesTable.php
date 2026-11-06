<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Filament\Support\CsvExport;
use App\Filament\Support\DateRangeFilter;
use App\Http\Requests\StoreContactRequest;
use App\Models\City;
use App\Models\ContactMessage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    /** Columns written to the CSV export */
    public static function exportColumns(): array
    {
        return [
            'Date' => fn (ContactMessage $r) => $r->created_at?->format('Y-m-d H:i'),
            'Name' => fn (ContactMessage $r) => $r->name,
            'Phone' => fn (ContactMessage $r) => $r->phone,
            'Email' => fn (ContactMessage $r) => $r->email,
            'Topic' => fn (ContactMessage $r) => $r->topicLabel(),
            'City' => fn (ContactMessage $r) => $r->cityLabel(),
            'Area' => fn (ContactMessage $r) => $r->areaLabel(),
            'Message' => fn (ContactMessage $r) => $r->message,
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
                TextColumn::make('topic')
                    ->formatStateUsing(fn (ContactMessage $record) => $record->topicLabel())
                    ->badge()
                    ->color(fn (string $state) => $state === 'partner' ? 'primary' : 'gray'),
                TextColumn::make('city')
                    ->formatStateUsing(fn (ContactMessage $record) => $record->cityLabel())
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('area')
                    ->formatStateUsing(fn (ContactMessage $record) => $record->areaLabel())
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('message')
                    ->limit(60)
                    ->tooltip(fn (ContactMessage $record) => $record->message)
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('topic')
                    ->options(StoreContactRequest::TOPICS)
                    ->multiple(),
                SelectFilter::make('city')
                    ->options(fn () => City::options())
                    ->searchable(),
                DateRangeFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CsvExport::bulkAction('contact-messages', self::exportColumns()),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No contact messages yet')
            ->emptyStateDescription('Messages sent from the Contact page will appear here.');
    }
}
