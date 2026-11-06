<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * The builder logo strips on Home, About and Partner show different builders, so the same 8 logos don't repeat
 * on every page: Home shows the partner builders; About and Partner each show a different set of the other
 * builders (most projects first). Only builders with a logo and at least one live project are used, and a page
 * is topped up from the rest when there aren't enough.
 */
class BuilderStrip
{
    public const SIZE = 8;

    /** @return Collection<int, array<string, mixed>> */
    public static function for(string $page): Collection
    {
        $catalog = Projects::catalog();
        $live = Projects::developers()
            ->filter(fn (array $d) => $d['logo'] && $catalog->contains('developer', $d['slug']))
            ->sortByDesc(fn (array $d) => [$catalog->where('developer', $d['slug'])->count(), $d['partner']])
            ->values();

        $partners = $live->where('partner', true)->values();
        $others = $live->where('partner', false)->values();

        $picked = (match ($page) {
            'home' => $partners,
            'about' => $others->slice(0, self::SIZE),
            'partner' => $others->slice(self::SIZE, self::SIZE),
            default => $live,
        })->take(self::SIZE)->values();

        // Not enough builders for this page: fill up with any not shown yet
        return $picked->concat($live->whereNotIn('slug', $picked->pluck('slug'))->take(self::SIZE - $picked->count()))->values();
    }
}
