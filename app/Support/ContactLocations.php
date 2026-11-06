<?php

namespace App\Support;

use App\Models\Area;
use App\Models\City;
use Illuminate\Support\Str;

/**
 * City and area choices for the Contact form, from the admin's Cities and Areas: only areas with at least one
 * published project, and only cities that have such an area. Buyers who haven't decided can pick "Not sure yet".
 */
class ContactLocations
{
    public const NOT_SURE = 'not-sure';

    public const NOT_SURE_LABEL = 'Not sure yet';

    /** @return array<string, array{name: string, areas: array<string, string>}> city slug => name and its areas (slug => name) */
    public static function all(): array
    {
        return once(function () {
            $areas = Area::query()
                ->whereHas('projects', fn ($q) => $q->where('is_published', true))
                ->ordered()
                ->get()
                ->groupBy('city_slug');

            $fromAdmin = City::query()->whereIn('slug', $areas->keys())->ordered()->get()
                ->mapWithKeys(fn (City $city) => [$city->slug => [
                    'name' => $city->name,
                    'areas' => $areas[$city->slug]->pluck('name', 'slug')->all(),
                ]])
                ->all();

            // The admin has no projects yet (e.g. before the real data is imported): offer the places of the projects
            // the site shows, so the form always works
            // No projects anywhere yet: every city in Pakistan, each with only "Not sure yet", so people can still write in
            return $fromAdmin ?: (static::fromListedProjects() ?: collect(PakistanCities::all())
                ->map(fn (string $name) => ['name' => $name, 'areas' => []])->all());
        });
    }

    /** @return array<string, array{name: string, areas: array<string, string>}> cities and areas of the listed projects */
    private static function fromListedProjects(): array
    {
        return Projects::catalog()
            ->groupBy('city')
            ->sortBy(fn ($projects, $city) => array_search($city, array_keys(Projects::cities()), true))
            ->map(fn ($projects, $city) => [
                'name' => Projects::cityName($city),
                'areas' => $projects->mapWithKeys(fn ($p) => [Str::slug($p['area']) => $p['area']])->sort()->all(),
            ])
            ->all();
    }

    /** @return list<string> */
    public static function areaSlugsFor(?string $city): array
    {
        return array_keys(self::all()[$city]['areas'] ?? []);
    }

    public static function areaLabel(?string $slug): ?string
    {
        return match ($slug) {
            null => null,
            self::NOT_SURE => self::NOT_SURE_LABEL,
            // All area names in one query per request, not one per table row or CSV line (C7)
            default => once(fn () => Area::query()->pluck('name', 'slug')->all())[$slug]
                ?? collect(static::all())->pluck('areas')->collapse()->get($slug, $slug),
        };
    }
}
