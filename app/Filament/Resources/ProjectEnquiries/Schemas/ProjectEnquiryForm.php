<?php

namespace App\Filament\Resources\ProjectEnquiries\Schemas;

use App\Http\Requests\StoreEnquiryRequest;
use App\Support\Projects;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProjectEnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enquiry')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('project_slug')
                            ->label('Project')
                            ->options(fn () => Projects::all()->sortBy('name')->pluck('name', 'slug')->all())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('project_name', $state ? ((Projects::find($state) ?? [])['name'] ?? $state) : null)),
                        Select::make('interest')
                            ->label('Interested in')
                            ->options(StoreEnquiryRequest::INTERESTS)
                            ->default('general')
                            ->required(),
                        TextInput::make('project_name')
                            ->label('Project name (as recorded)')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Filled automatically from the project; kept even if the project is later renamed.'),
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
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
