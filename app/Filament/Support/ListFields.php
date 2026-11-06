<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

/** Name and web-address fields shared by the list resources. */
class ListFields
{
    public static function name(string $table, string $hint): TextInput
    {
        return TextInput::make('name')
            ->required()
            ->maxLength(80)
            ->unique($table, 'name', ignoreRecord: true)
            ->helperText($hint);
    }

    /** @param  int  $maxLength  the database column's length (A1): 40 for cities and types, 30 for stages */
    public static function slug(string $filter, int $maxLength = 40): TextInput
    {
        return TextInput::make('slug')
            ->label('Web address')
            ->prefix("/projects?{$filter}=")
            ->maxLength($maxLength)
            ->alphaDash()
            ->unique(ignoreRecord: true)
            ->placeholder('Filled from the name')
            ->helperText('Leave empty to fill it from the name. Changing it later breaks old links; its projects move with it.');
    }

    public static function projectsColumn(): TextColumn
    {
        return TextColumn::make('projects_count')->label('Projects')->sortable()->alignEnd();
    }
}
