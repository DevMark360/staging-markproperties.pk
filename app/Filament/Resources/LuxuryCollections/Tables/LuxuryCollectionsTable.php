<?php

namespace App\Filament\Resources\LuxuryCollections\Tables;

use App\Models\LuxuryCollection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LuxuryCollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('items'))
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginated(false)
            ->columns([
                ImageColumn::make('cover')
                    ->label('')
                    ->disk('site')
                    ->imageWidth(72)
                    ->imageHeight(48)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),
                TextColumn::make('name')
                    ->weight('medium')
                    ->description(fn (LuxuryCollection $record) => $record->tagline)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('items_count')->label('Projects')->alignEnd(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('No Luxury collections yet')
            ->emptyStateDescription('Add a collection, or run "php artisan catalogue:import" to copy the website collections here.');
    }
}
