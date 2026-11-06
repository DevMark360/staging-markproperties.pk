<?php

namespace App\Support;

use App\Models\Stage;

class Nav
{
    /**
     * The navigation with every link that would lead to an empty list removed: a stage, city or property type with
     * no live project, or a Luxury collection with none. A dropdown group left with no links goes too.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function items(): array
    {
        return once(fn () => collect(config('site.nav'))->map(function (array $item) {
            if (empty($item['columns'])) {
                return $item;
            }
            $item['columns'] = collect($item['columns'])
                ->map(fn (array $column) => static::fromDatabase($item, $column))
                ->map(fn (array $column) => [...$column, 'links' => array_values(array_filter($column['links'], [self::class, 'leadsSomewhere']))])
                ->filter(fn (array $column) => $column['links'])
                ->values()->all();

            return $item;
        })->filter(fn (array $item) => ! array_key_exists('columns', $item) || $item['columns'])->values()->all());
    }

    /**
     * Menu groups whose links come from the admin: "By stage" lists the stages set to show in the menu (after "All New
     * Projects"), "By city" the cities with projects, and the Luxury menu the collections (after "All Luxury Projects").
     */
    private static function fromDatabase(array $item, array $column): array
    {
        if (($column['heading'] ?? null) === 'By stage') {
            $column['links'] = [
                ...array_filter($column['links'], fn ($link) => ! isset($link['params']['status'])),
                ...Stage::query()->ordered()->where('show_in_menu', true)->pluck('name', 'slug')
                    ->map(fn ($name, $slug) => ['label' => $name, 'route' => 'projects.index', 'params' => ['status' => $slug]])->values()->all(),
            ];
        }
        if (($column['heading'] ?? null) === 'By city') {
            $column['links'] = collect(Projects::liveCities())
                ->map(fn ($name, $slug) => ['label' => $name, 'route' => 'projects.index', 'params' => ['city' => $slug]])
                ->values()->all();
        }
        if ($item['route'] === 'luxe') {
            $column['links'] = [
                ...array_filter($column['links'], fn ($link) => ! isset($link['params']['collection'])),
                ...Projects::luxuryCollections()->map(fn ($c) => ['label' => $c['name'], 'route' => 'luxe', 'params' => ['collection' => $c['slug']]])->all(),
            ];
        }

        return $column;
    }

    /** False for a link to an empty project list or an empty Luxury collection. */
    public static function leadsSomewhere(array $link): bool
    {
        $params = $link['params'] ?? [];

        return match (true) {
            $link['route'] === 'projects.index' && $params !== [] => Projects::hasProjects($params),
            $link['route'] === 'luxe' && isset($params['collection']) => in_array($params['collection'], Projects::liveLuxuryCollections(), true),
            default => true,
        };
    }

    /**
     * URL for a navigation link from config('site.nav'): route + optional params and #fragment.
     *
     * @param  array{route: string, params?: array<string, string>, fragment?: string}  $link
     */
    public static function href(array $link): string
    {
        $url = route($link['route'], $link['params'] ?? []);

        return isset($link['fragment']) ? $url.'#'.$link['fragment'] : $url;
    }

    /**
     * Whether a nav item points at the current page: its route, a sibling route like projects.*,
     * or any link inside its dropdown columns.
     *
     * @param  array{route: string, columns?: array}  $item
     */
    public static function isActive(array $item): bool
    {
        $routes = collect($item['columns'] ?? [])
            ->flatMap(fn (array $column) => array_column($column['links'], 'route'))
            ->push($item['route'])
            ->flatMap(fn (string $route) => [$route, str($route)->before('.').'.*'])
            ->unique();

        return request()->routeIs(...$routes);
    }
}
