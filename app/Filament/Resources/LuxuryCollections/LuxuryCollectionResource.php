<?php

namespace App\Filament\Resources\LuxuryCollections;

use App\Filament\Resources\LuxuryCollections\Pages\CreateLuxuryCollection;
use App\Filament\Resources\LuxuryCollections\Pages\EditLuxuryCollection;
use App\Filament\Resources\LuxuryCollections\Pages\ListLuxuryCollections;
use App\Filament\Resources\LuxuryCollections\Schemas\LuxuryCollectionForm;
use App\Filament\Resources\LuxuryCollections\Tables\LuxuryCollectionsTable;
use App\Models\LuxuryCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LuxuryCollectionResource extends Resource
{
    protected static ?string $model = LuxuryCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Luxury collection';

    protected static ?string $pluralModelLabel = 'Luxury collections';

    public static function form(Schema $schema): Schema
    {
        return LuxuryCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LuxuryCollectionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLuxuryCollections::route('/'),
            'create' => CreateLuxuryCollection::route('/create'),
            'edit' => EditLuxuryCollection::route('/{record}/edit'),
        ];
    }
}
