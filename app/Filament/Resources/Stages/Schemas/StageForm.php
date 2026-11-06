<?php

namespace App\Filament\Resources\Stages\Schemas;

use App\Filament\Support\ListFields;
use App\Models\Stage;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                ListFields::name('stages', 'e.g. “Pre-Launch”.'),
                ListFields::slug('status', Stage::SLUG_LENGTH),
                Textarea::make('description')
                    ->rows(2)
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->helperText('One line shown with this stage on the website, e.g. on the home page stage cards.'),
                Toggle::make('show_in_menu')
                    ->label('Show in the website menu')
                    ->default(true)
                    ->helperText('Hidden automatically while no published project is at this stage.'),
            ]),
        ]);
    }
}
