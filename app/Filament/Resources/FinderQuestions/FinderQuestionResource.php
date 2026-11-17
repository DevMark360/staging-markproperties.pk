<?php

namespace App\Filament\Resources\FinderQuestions;

use App\Filament\Resources\FinderQuestions\Pages\CreateFinderQuestion;
use App\Filament\Resources\FinderQuestions\Pages\EditFinderQuestion;
use App\Filament\Resources\FinderQuestions\Pages\ListFinderQuestions;
use App\Filament\Resources\FinderQuestions\Schemas\FinderQuestionForm;
use App\Filament\Resources\FinderQuestions\Tables\FinderQuestionsTable;
use App\Models\FinderQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/** The questions the home page property finder pop-up asks, in order */
class FinderQuestionResource extends Resource
{
    protected static ?string $model = FinderQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Property finder';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'finder question';

    protected static ?string $pluralModelLabel = 'property finder questions';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return FinderQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinderQuestionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinderQuestions::route('/'),
            'create' => CreateFinderQuestion::route('/create'),
            'edit' => EditFinderQuestion::route('/{record}/edit'),
        ];
    }
}
