<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class HomeSlider extends SectionOrderPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $title = 'Home slider';

    protected static ?int $navigationSort = 5;

    protected static function section(): string
    {
        return 'home';
    }
}
