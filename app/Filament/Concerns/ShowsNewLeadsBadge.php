<?php

namespace App\Filament\Concerns;

/**
 * Sidebar badge for a lead list: how many arrived in the last 7 days (no badge when none), so new leads stand out
 * without opening each list.
 */
trait ShowsNewLeadsBadge
{
    public const NEW_LEAD_DAYS = 7;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('created_at', '>=', now()->subDays(self::NEW_LEAD_DAYS))->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New in the last '.self::NEW_LEAD_DAYS.' days';
    }
}
