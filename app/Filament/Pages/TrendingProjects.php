<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class TrendingProjects extends SectionOrderPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?string $title = 'Trending';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'trending';

    protected static function section(): string
    {
        return 'trending';
    }
}
