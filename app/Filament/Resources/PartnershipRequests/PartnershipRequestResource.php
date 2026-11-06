<?php

namespace App\Filament\Resources\PartnershipRequests;

use App\Filament\Resources\PartnershipRequests\Pages\CreatePartnershipRequest;
use App\Filament\Resources\PartnershipRequests\Pages\EditPartnershipRequest;
use App\Filament\Resources\PartnershipRequests\Pages\ListPartnershipRequests;
use App\Filament\Resources\PartnershipRequests\Schemas\PartnershipRequestForm;
use App\Filament\Resources\PartnershipRequests\Tables\PartnershipRequestsTable;
use App\Models\PartnershipRequest;
use App\Filament\Concerns\ShowsNewLeadsBadge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PartnershipRequestResource extends Resource
{
    use ShowsNewLeadsBadge;

    protected static ?string $model = PartnershipRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'partnership request';

    protected static ?string $pluralModelLabel = 'partnership requests';

    protected static ?string $recordTitleAttribute = 'company';

    public static function form(Schema $schema): Schema
    {
        return PartnershipRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnershipRequestsTable::configure($table);
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
            'index' => ListPartnershipRequests::route('/'),
            'create' => CreatePartnershipRequest::route('/create'),
            'edit' => EditPartnershipRequest::route('/{record}/edit'),
        ];
    }
}
