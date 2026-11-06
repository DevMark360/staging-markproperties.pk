<?php

namespace App\Filament\Resources\LaunchAlerts;

use App\Filament\Resources\LaunchAlerts\Pages\CreateLaunchAlert;
use App\Filament\Resources\LaunchAlerts\Pages\EditLaunchAlert;
use App\Filament\Resources\LaunchAlerts\Pages\ListLaunchAlerts;
use App\Filament\Resources\LaunchAlerts\Schemas\LaunchAlertForm;
use App\Filament\Resources\LaunchAlerts\Tables\LaunchAlertsTable;
use App\Models\LaunchAlert;
use App\Filament\Concerns\ShowsNewLeadsBadge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LaunchAlertResource extends Resource
{
    use ShowsNewLeadsBadge;

    protected static ?string $model = LaunchAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'launch-alert subscriber';

    protected static ?string $pluralModelLabel = 'launch alerts';

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return LaunchAlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaunchAlertsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaunchAlerts::route('/'),
            'create' => CreateLaunchAlert::route('/create'),
            'edit' => EditLaunchAlert::route('/{record}/edit'),
        ];
    }
}
