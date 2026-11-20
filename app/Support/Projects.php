<?php

namespace App\Support;

use App\Models\Area;
use App\Models\City;
use App\Models\Developer;
use App\Models\LuxuryCollection;
use App\Models\Project;
use App\Models\PropertyType;
use App\Models\Stage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Everything the public site shows about projects, read from the admin database: projects (published only),
 * developers, cities, areas, property types and Luxury collections. Each is read once per request.
 *
 * Projects come back in the array shape the views use (the same keys the old config/projects.php had), so a page
 * doesn't care where they're stored. Image, logo and brochure values are paths under public/ (images/… for the
 * imported files, uploads/… for admin uploads); use fileUrl() / imageUrl() to link them.
 */
class Projects
{
    /** Older menu links use finer commercial types (used only when the admin has a "commercial" type) */
    private const TYPE_ALIASES = ['shops' => 'commercial', 'offices' => 'commercial'];

    /**
     * Published projects, newest first, with their page details.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function all(): Collection
    {
        return once(fn () => Project::query()
            ->where('is_published', true)
            ->with(['amenityItems', 'utilityItems'])
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Project $project) => static::shape($project))
            ->values());
    }

    /** One project in the array shape the views use. */
    private static function shape(Project $p): array
    {
        $names = fn ($items, $fallback) => $items->isNotEmpty() ? $items->pluck('name')->all() : array_values($fallback ?? []);

        return [
            'name' => $p->name,
            'slug' => $p->slug,
            'developer' => $p->developer_slug,
            'status' => $p->stage_slug,
            'city' => $p->city_slug,
            'area' => $p->area,
            'area_slug' => $p->area_slug ?: Str::slug((string) $p->area),
            'address' => $p->address,
            'type' => $p->type_slug,
            'image' => $p->image,
            'hero' => $p->hero_image ?? Project::largeVersion($p->image),
            'views' => 0,
            'description' => (string) $p->description,
            'highlights' => $p->highlights ?: null,
            'amenities' => $names($p->amenityItems, $p->amenities),
            'utilities' => $names($p->utilityItems, $p->utilities),
            'gallery' => array_values($p->gallery ?? []),
            'latitude' => $p->latitude,
            'longitude' => $p->longitude,
            'map_embed' => $p->map_embed,
            'brochure' => $p->brochure,
        ];
    }

    /**
     * The listing data (all() already has the type and coordinates).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function catalog(): Collection
    {
        return static::all();
    }

    /**
     * A published project with its page details, or null.
     *
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        return once(fn () => static::all()->keyBy('slug')->all())[$slug] ?? null;
    }

    /**
     * Developers, A–Z: slug, name, logo (path), partner, about.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function developers(): Collection
    {
        return once(fn () => Developer::query()->orderBy('name')->get()->map(fn (Developer $d) => [
            'slug' => $d->slug,
            'name' => $d->name,
            'logo' => $d->logo,
            'partner' => (bool) $d->is_partner,
            'about' => $d->about,
        ])->values());
    }

    /** @return array<string, string> city slug => name, in the admin's order */
    public static function cities(): array
    {
        return once(fn () => City::options());
    }

    /** @return array<string, string> property type slug => label, from the admin's Property types */
    public static function types(): array
    {
        return once(fn () => PropertyType::options());
    }

    /** @return array<string, string> stage slug => name, from the admin's Stages, in its order */
    public static function stages(): array
    {
        return once(fn () => Stage::options());
    }

    /**
     * Luxury collections in the admin's order: slug, name, tagline, description, cover (path) and the slugs of their
     * published projects, in order.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function luxuryCollections(): Collection
    {
        return once(function () {
            $live = static::all()->pluck('slug')->flip();

            return LuxuryCollection::query()->with('items.project')->orderBy('sort_order')->orderBy('name')->get()
                ->map(fn (LuxuryCollection $c) => [
                    'slug' => $c->slug,
                    'name' => $c->name,
                    'tagline' => $c->tagline,
                    'description' => $c->description,
                    'cover' => $c->cover,
                    'projects' => $c->items->map(fn ($item) => $item->project?->slug)->filter(fn ($slug) => $slug && $live->has($slug))->values()->all(),
                ])->values();
        });
    }

    /**
     * Areas switched on for the home page ("Where Pakistan is building next"), in their order, with how many
     * published projects each has.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function homeAreas(): Collection
    {
        return once(fn () => Area::query()->where('show_on_home', true)->orderBy('home_order')->get()
            ->map(fn (Area $a) => [
                'name' => $a->name,
                'slug' => $a->slug,
                'city' => $a->city_slug,
                'description' => $a->description,
                'image' => $a->image,
                'projects' => static::all()->where('area_slug', $a->slug)->count(),
            ])->values());
    }

    /** An area's name from its web address (the admin's Areas, then the projects' own area names). */
    public static function areaName(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        return once(fn () => Area::query()->pluck('name', 'slug')->all())[$slug]
            ?? static::all()->firstWhere('area_slug', $slug)['area'] ?? null;
    }

    /*
    | What the public site may offer: only cities, property types, stages and Luxury collections with at least one
    | live project. Anything empty is never listed, linked or offered as a filter.
    */

    /** @return array<string, string> city slug => name, in the admin's order */
    public static function liveCities(): array
    {
        return once(function () {
            $used = static::all()->pluck('city')->unique();

            // Cities in the admin's order first; any city a project uses but the list lacks still shows, by name
            return collect(static::cities())->only($used)->all()
                + $used->diff(array_keys(static::cities()))->mapWithKeys(fn ($c) => [$c => static::cityName($c)])->all();
        });
    }

    /** @return array<string, string> property type key => label */
    public static function liveTypes(): array
    {
        return once(fn () => collect(static::types())->filter(fn ($label, $type) => static::all()->contains('type', $type))->all());
    }

    /** @return array<string, string> stage slug => name, only stages with at least one live project */
    public static function liveStages(): array
    {
        return once(fn () => collect(static::stages())->filter(fn ($name, $slug) => static::all()->contains('status', $slug))->all());
    }

    /** @return list<string> slugs of the Luxury collections with at least one live project */
    public static function liveLuxuryCollections(): array
    {
        return once(fn () => static::luxuryCollections()->filter(fn ($c) => $c['projects'])->pluck('slug')->values()->all());
    }

    /** Whether a project-list address (city, type, status, area, developer…) would show at least one project. */
    public static function hasProjects(array $query): bool
    {
        return static::search(static::filters($query))->isNotEmpty();
    }

    /** A city's name from the admin's Cities, or a readable name made from its slug (N4). */
    public static function cityName(?string $slug): ?string
    {
        return $slug === null ? null : (static::cities()[$slug] ?? Str::headline($slug));
    }

    /** A property type's name from the admin's Property types, or a readable name made from its slug. */
    public static function typeName(?string $type): ?string
    {
        return $type === null ? null : (static::types()[$type] ?? Str::headline($type));
    }

    /** A stage's name from the admin's Stages; null for a stage that isn't in the list (no badge is shown). */
    public static function stageName(?string $slug): ?string
    {
        return $slug === null ? null : (static::stages()[$slug] ?? null);
    }

    /**
     * Map markers for these projects (those with coordinates): what the map card and photo marker need.
     *
     * @param  Collection<int, array<string, mixed>>  $projects
     * @return Collection<int, array<string, mixed>>
     */
    public static function mapPoints(Collection $projects): Collection
    {
        $developers = static::developers()->keyBy('slug');

        return $projects->filter(fn ($p) => $p['latitude'] !== null && $p['longitude'] !== null)->map(fn ($p) => [
            'slug' => $p['slug'],
            'name' => $p['name'],
            'url' => route('projects.show', $p['slug']),
            'city' => $p['city'], // the map opens on the city with the most projects
            'lat' => $p['latitude'],
            'lng' => $p['longitude'],
            'image' => static::imageUrl($p['image']),
            'stage' => static::stageName($p['status']),
            'developer' => $developers[$p['developer']]['name'] ?? null,
            'logo' => static::fileUrl($developers[$p['developer']]['logo'] ?? null, 'images/developers'),
            'location' => $p['area'] === self::cityName($p['city']) ? $p['area'] : $p['area'].', '.self::cityName($p['city']),
        ])->values();
    }

    /**
     * Normalised listing filters from a query string; unknown values are dropped.
     *
     * @param  array<string, mixed>  $query
     * @return array{q: string, city: ?string, type: ?string, status: ?string, developer: ?string, area: ?string, sort: string}
     */
    public static function filters(array $query): array
    {
        $pick = fn (string $key, array $allowed) => in_array($value = $query[$key] ?? null, $allowed, true) ? $value : null;
        $type = $query['type'] ?? null;

        return [
            'q' => trim(mb_substr((string) ($query['q'] ?? ''), 0, 80)),
            'city' => $pick('city', array_keys(static::cities() + static::liveCities())),
            'type' => $pick('type', array_keys(static::types())) ?? (isset(static::types()[self::TYPE_ALIASES[$type] ?? '']) ? self::TYPE_ALIASES[$type] : null),
            'status' => $pick('status', array_keys(static::stages())),
            'developer' => $pick('developer', static::developers()->pluck('slug')->all()),
            'area' => is_string($query['area'] ?? null) ? str($query['area'])->slug()->toString() ?: null : null,
            'sort' => $pick('sort', ['newest', 'name']) ?? 'newest',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters  from filters()
     * @return Collection<int, array<string, mixed>>
     */
    public static function search(array $filters): Collection
    {
        $developers = static::developers()->keyBy('slug');
        $needle = str($filters['q'])->lower()->toString();

        $results = static::all()->filter(fn (array $p) => (! $filters['city'] || $p['city'] === $filters['city'])
            && (! $filters['type'] || $p['type'] === $filters['type'])
            && (! $filters['status'] || $p['status'] === $filters['status'])
            && (! $filters['developer'] || $p['developer'] === $filters['developer'])
            // An area is the admin's Area the project belongs to (a home page area can group several neighbourhoods)
            && (! $filters['area'] || $p['area_slug'] === $filters['area'] || Str::slug((string) $p['area']) === $filters['area'])
            && ($needle === '' || str_contains(str(implode(' ', [
                $p['name'], $p['area'], $p['address'],
                self::cityName($p['city']),
                $developers[$p['developer']]['name'] ?? '',
            ]))->lower()->toString(), $needle)));

        return ($filters['sort'] === 'name' ? $results->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) : $results)->values();
    }

    /**
     * URL for a project image: a full path under public/ (images/… or uploads/…), or a bare file name in
     * images/$folder/ (the old built-in data).
     */
    public static function imageUrl(?string $value, string $folder = 'projects'): ?string
    {
        return static::fileUrl($value, "images/{$folder}");
    }

    /** URL for any stored file: a path under public/, or a bare file name inside $directory. */
    public static function fileUrl(?string $value, string $directory): ?string
    {
        return $value ? asset(str_contains($value, '/') ? $value : "{$directory}/{$value}") : null;
    }

    /**
     * Other projects in the same area first, then by the same developer, then the same city.
     *
     * @param  array<string, mixed>  $project
     * @return Collection<int, array<string, mixed>>
     */
    public static function similar(array $project, int $limit = 8): Collection
    {
        return static::all()
            ->reject(fn (array $other) => $other['slug'] === $project['slug'])
            ->map(fn (array $other) => [
                $other,
                ($other['area'] === $project['area']) * 4
                    + ($other['developer'] === $project['developer']) * 2
                    + ($other['city'] === $project['city']),
            ])
            ->filter(fn (array $pair) => $pair[1] > 0)
            ->sortByDesc(fn (array $pair) => $pair[1])
            ->take($limit)
            ->map(fn (array $pair) => $pair[0])
            ->values();
    }
}
