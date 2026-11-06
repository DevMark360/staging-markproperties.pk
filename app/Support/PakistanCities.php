<?php

namespace App\Support;

use Illuminate\Support\Str;

/** Every city in config/pakistan_cities.php, for the Partner With Us form (builders can be anywhere in Pakistan). */
class PakistanCities
{
    /** @return array<string, array<string, string>> province => [slug => name], cities in A–Z order */
    public static function byProvince(): array
    {
        return once(fn () => collect(config('pakistan_cities'))
            ->map(fn (array $cities) => collect($cities)->sort()->mapWithKeys(fn (string $name) => [Str::slug($name) => $name])->all())
            ->all());
    }

    /** @return array<string, string> slug => name */
    public static function all(): array
    {
        return once(fn () => array_merge(...array_values(static::byProvince())));
    }

    /** A city's name: from this list, the site's cities, or the slug itself. */
    public static function name(?string $slug): ?string
    {
        return $slug === null ? null : (static::all()[$slug] ?? $slug);
    }
}
