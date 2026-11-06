<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Filament\Support\ListFields;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                ListFields::name('cities', 'e.g. “Karachi”. The city menu only lists cities that have projects.'),
                ListFields::slug('city'),
            ]),
        ]);
    }
}
