<?php

namespace App\Filament\Resources\Stages;

use App\Filament\Resources\Stages\Pages\CreateStage;
use App\Filament\Resources\Stages\Pages\EditStage;
use App\Filament\Resources\Stages\Pages\ListStages;
use App\Filament\Resources\Stages\Schemas\StageForm;
use App\Filament\Resources\Stages\Tables\StagesTable;
use App\Models\Stage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'Lists';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'stage';

    protected static ?string $pluralModelLabel = 'stages';

    public static function form(Schema $schema): Schema
    {
        return StageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStages::route('/'),
            'create' => CreateStage::route('/create'),
            'edit' => EditStage::route('/{record}/edit'),
        ];
    }
}
