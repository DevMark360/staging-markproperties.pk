<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestEnquiries;
use App\Filament\Widgets\LeadStats;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

/** The admin home: this week's leads at a glance, then the latest project enquiries. */
class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = -1;

    public function getWidgets(): array
    {
        return [LeadStats::class, LatestEnquiries::class];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
