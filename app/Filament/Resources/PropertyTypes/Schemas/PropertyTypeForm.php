<?php

namespace App\Filament\Resources\PropertyTypes\Schemas;

use App\Filament\Support\ListFields;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PropertyTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                ListFields::name('property_types', 'e.g. “Apartments”.'),
                ListFields::slug('type'),
                Toggle::make('show_in_menu')
                    ->label('Show in the website menu')
                    ->default(true)
                    ->helperText('Hidden automatically while no published project has this type.'),
            ]),
        ]);
    }
}
