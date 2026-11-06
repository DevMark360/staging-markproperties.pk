<?php

namespace App\Filament\Resources\Utilities;

use App\Filament\Resources\Utilities\Pages\CreateUtility;
use App\Filament\Resources\Utilities\Pages\EditUtility;
use App\Filament\Resources\Utilities\Pages\ListUtilities;
use App\Filament\Support\FeatureList;
use App\Models\Utility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UtilityResource extends Resource
{
    protected static ?string $model = Utility::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Lists';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'utility';

    protected static ?string $pluralModelLabel = 'utilities';

    public static function form(Schema $schema): Schema
    {
        return FeatureList::form($schema, 'utility');
    }

    public static function table(Table $table): Table
    {
        return FeatureList::table($table, Utility::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUtilities::route('/'),
            'create' => CreateUtility::route('/create'),
            'edit' => EditUtility::route('/{record}/edit'),
        ];
    }
}
