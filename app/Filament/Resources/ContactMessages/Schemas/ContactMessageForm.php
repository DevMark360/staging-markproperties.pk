<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Http\Requests\StoreContactRequest;
use App\Models\Area;
use App\Models\City;
use App\Support\ContactLocations;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('phone')
                            ->label('Number')
                            ->tel()
                            ->required()
                            ->regex('/^\+?[0-9\s\-()]{10,18}$/')
                            ->maxLength(30),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(120),
                        Select::make('topic')
                            ->options(StoreContactRequest::TOPICS)
                            ->default('buying')
                            ->required(),
                        Select::make('city')
                            ->options(fn () => City::options())
                            ->searchable()
                            ->live()
                            ->placeholder('Any city'),
                        // The areas of the chosen city, plus "Not sure yet" (what the Contact form offers)
                        Select::make('area')
                            ->options(fn (Get $get) => Area::optionsFor($get('city')) + [ContactLocations::NOT_SURE => ContactLocations::NOT_SURE_LABEL])
                            ->searchable()
                            ->placeholder('No area'),
                        Textarea::make('message')
                            ->rows(5)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
