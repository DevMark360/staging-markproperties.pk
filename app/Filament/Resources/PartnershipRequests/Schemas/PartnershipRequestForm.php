<?php

namespace App\Filament\Resources\PartnershipRequests\Schemas;

use App\Models\City;
use App\Models\PropertyType;
use App\Models\Stage;
use App\Support\PakistanCities;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnershipRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('company')
                            ->label('Company name')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('project')
                            ->label('Project name')
                            ->maxLength(120),
                        Select::make('stage')
                            ->options(fn () => Stage::options()),
                        Select::make('type')
                            ->label('Project type')
                            ->options(fn () => PropertyType::options()),
                        Select::make('city')
                            ->options(fn () => PakistanCities::byProvince()) // every city in Pakistan, like the form
                            ->searchable()
                            ->required(),
                    ]),
                Section::make('Contact person')
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
                        Textarea::make('message')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
