<?php

namespace App\Filament\Resources\BlogCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema(self::fields()),
        ]);
    }

    /** Also used by the "add a category" option inside the blog post form. */
    public static function fields(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(80)
                ->unique('blog_categories', 'name', ignoreRecord: true)
                ->helperText('Shown as a filter on the blog page, e.g. “Buyer guides”.'),
        ];
    }
}
