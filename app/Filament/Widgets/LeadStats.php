<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\LaunchAlerts\LaunchAlertResource;
use App\Filament\Resources\PartnershipRequests\PartnershipRequestResource;
use App\Filament\Resources\ProjectEnquiries\ProjectEnquiryResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/** One card per kind of lead: how many arrived in the last 7 days, how many today, and a 7-day trend line. */
class LeadStats extends StatsOverviewWidget
{
    private const DAYS = 7;

    protected ?string $heading = 'Leads in the last 7 days';

    /** Opening the dashboard should show fresh numbers, but there is no need to poll while it stays open */
    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        return [
            $this->stat('Project enquiries', ProjectEnquiryResource::class),
            $this->stat('Contact messages', ContactMessageResource::class),
            $this->stat('Partner requests', PartnershipRequestResource::class),
            $this->stat('Launch alert sign-ups', LaunchAlertResource::class),
        ];
    }

    /** @param  class-string<\Filament\Resources\Resource>  $resource */
    private function stat(string $label, string $resource): Stat
    {
        $since = Carbon::today()->subDays(self::DAYS - 1);
        $perDay = $resource::getModel()::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // One point per day, oldest first, with 0 for quiet days
        $series = collect(range(self::DAYS - 1, 0))->map(fn (int $ago) => (int) ($perDay[Carbon::today()->subDays($ago)->toDateString()] ?? 0));

        return Stat::make($label, $series->sum())
            ->description($series->last().' today')
            ->chart($series->all())
            ->chartColor('primary')
            ->descriptionColor('gray')
            ->url($resource::getUrl());
    }
}
