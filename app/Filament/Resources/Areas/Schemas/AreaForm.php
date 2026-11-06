<?php

namespace App\Filament\Resources\Areas\Schemas;

use App\Models\City;
use App\Rules\ReadableImage;
use App\Support\ImageResizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema(self::fields()),
            Section::make('Home page')
                ->description('Areas switched on here appear in “Where Pakistan is building next”. Set their order on the Areas list.')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('show_on_home')
                        ->label('Show on the home page')
                        ->live()
                        ->columnSpanFull(),
                    FileUpload::make('image')
                        ->label('Photo')
                        ->image()
                        ->acceptedFileTypes(ReadableImage::TYPES) // JPG, PNG or WebP only; checked before saving (C8)
                        ->rule(new ReadableImage)
                        ->disk('site')
                        ->visibility('public')
                        ->maxSize(15 * 1024)
                        ->imagePreviewHeight('160')
                        ->required(fn (Get $get) => $get('show_on_home'))
                        ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file, Get $get) => ImageResizer::store(
                            $file,
                            'uploads/areas',
                            Str::slug($get('name') ?: 'area').'-'.Str::lower(Str::random(6)),
                        )['card'])
                        ->helperText('A tall photo works best. Saved as WebP at 800px and 1600px wide.'),
                    Textarea::make('description')
                        ->rows(4)
                        ->maxLength(255)
                        ->required(fn (Get $get) => $get('show_on_home'))
                        ->helperText('One sentence about the area, shown on its home page card.'),
                ]),
        ]);
    }

    /** City, name and web address. Also used by "add an area" on the project form. */
    public static function fields(?string $city = null): array
    {
        return [
            Select::make('city_slug')
                ->label('City')
                ->options(fn () => City::options())
                ->default($city)
                ->searchable()
                ->required()
                ->live(),
            TextInput::make('name')
                ->required()
                ->maxLength(120)
                ->placeholder('e.g. Scheme 33')
                // The same name may exist once per city
                ->unique('areas', 'name', ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('city_slug', $get('city_slug'))),
            TextInput::make('slug')
                ->label('Web address')
                ->prefix('/projects?area=')
                ->maxLength(120)
                ->alphaDash()
                ->unique('areas', 'slug', ignoreRecord: true)
                ->placeholder('Filled from the name')
                ->helperText('Leave empty to fill it from the name.')
                ->columnSpanFull(),
        ];
    }
}
