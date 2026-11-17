<?php

namespace App\Filament\Resources\FinderLeads;

use App\Filament\Concerns\ShowsNewLeadsBadge;
use App\Filament\Resources\FinderLeads\Pages\ListFinderLeads;
use App\Filament\Resources\FinderLeads\Pages\ViewFinderLead;
use App\Filament\Resources\FinderLeads\Tables\FinderLeadsTable;
use App\Models\FinderLead;
use BackedEnum;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/** Leads from the home page property finder pop-up: details plus the answers they gave */
class FinderLeadResource extends Resource
{
    use ShowsNewLeadsBadge;

    protected static ?string $model = FinderLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Leads';

    protected static ?string $navigationLabel = 'Property finder';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'property finder lead';

    protected static ?string $pluralModelLabel = 'property finder leads';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contact')->columns(3)->columnSpanFull()->schema([
                TextEntry::make('name'),
                TextEntry::make('phone')->label('Number')->copyable(),
                TextEntry::make('email')->placeholder('—')->copyable(),
                TextEntry::make('created_at')->label('Sent')->dateTime('j M Y, g:i a'),
            ]),
            Section::make('Answers')->columnSpanFull()->schema([
                KeyValueEntry::make('answer_lines')
                    ->hiddenLabel()
                    ->state(fn (FinderLead $record) => $record->answerLines())
                    ->keyLabel('Question')
                    ->valueLabel('Answer')
                    ->placeholder('No answers'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return FinderLeadsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinderLeads::route('/'),
            'view' => ViewFinderLead::route('/{record}'),
        ];
    }
}
