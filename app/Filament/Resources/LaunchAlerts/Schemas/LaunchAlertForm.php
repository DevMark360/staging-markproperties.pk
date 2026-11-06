<?php

namespace App\Filament\Resources\LaunchAlerts\Schemas;

use App\Models\City;
use App\Models\LaunchAlert;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LaunchAlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscriber')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(120)
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['unique' => 'This email is already subscribed.'])
                            // Emails are saved in lower case, so "TAKEN@x.com" is the same subscriber as "taken@x.com"
                            ->rule(fn (?LaunchAlert $record) => function (string $attribute, $value, Closure $fail) use ($record) {
                                $taken = LaunchAlert::query()
                                    ->where('email', Str::lower(trim((string) $value)))
                                    ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                    ->exists();
                                if ($taken) {
                                    $fail('This email is already subscribed.');
                                }
                            }),
                        TextInput::make('phone')
                            ->label('Number')
                            ->tel()
                            ->required()
                            ->regex('/^\+?[0-9\s\-()]{10,18}$/')
                            ->maxLength(30)
                            // Not unique: subscribers are one per email, and two emails may share a number (R2)
                            ->helperText('Search the list by this number to see other sign-ups that use it.'),
                        Select::make('city')
                            ->options(fn () => City::options())
                            ->searchable()
                            ->placeholder('Any city'),
                    ]),
            ]);
    }
}
