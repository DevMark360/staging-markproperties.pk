<?php

namespace App\Filament\Resources\ProjectEnquiries;

use App\Filament\Resources\ProjectEnquiries\Pages\CreateProjectEnquiry;
use App\Filament\Resources\ProjectEnquiries\Pages\EditProjectEnquiry;
use App\Filament\Resources\ProjectEnquiries\Pages\ListProjectEnquiries;
use App\Filament\Resources\ProjectEnquiries\Schemas\ProjectEnquiryForm;
use App\Filament\Resources\ProjectEnquiries\Tables\ProjectEnquiriesTable;
use App\Models\ProjectEnquiry;
use App\Filament\Concerns\ShowsNewLeadsBadge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProjectEnquiryResource extends Resource
{
    use ShowsNewLeadsBadge;

    protected static ?string $model = ProjectEnquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'project enquiry';

    protected static ?string $pluralModelLabel = 'project enquiries';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProjectEnquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectEnquiriesTable::configure($table);
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
            'index' => ListProjectEnquiries::route('/'),
            'create' => CreateProjectEnquiry::route('/create'),
            'edit' => EditProjectEnquiry::route('/{record}/edit'),
        ];
    }
}
