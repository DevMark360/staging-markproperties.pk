<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Support\ProjectCopy;
use App\Support\Projects;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private const PER_PAGE = 24;

    public function index(Request $request): View
    {
        $filters = Projects::filters($request->query());
        $results = Projects::search($filters);
        $developers = Projects::developers()->keyBy('slug');
        $catalog = Projects::catalog();

        $page = max(1, (int) $request->query('page', 1));
        $projects = new LengthAwarePaginator(
            $results->forPage($page, self::PER_PAGE)->values(),
            $results->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => collect($request->query())->except(['page', 'view'])->all()],
        );

        // Everything that matches goes on the map, not just this page
        $points = Projects::mapPoints($results);

        $city = $filters['city'] ? Projects::cityName($filters['city']) : null;
        $areaName = Projects::areaName($filters['area']);

        return view('pages.projects.index', [
            'projects' => $projects,
            'points' => $points,
            'filters' => $filters,
            'heading' => 'Off-plan projects in '.($areaName ? $areaName.', ' : '').($city ?? 'Pakistan'),
            'developers' => $developers,
            // Filter options: cities with projects, plus the selected one so an empty city still shows as chosen
            'cities' => collect(Projects::liveCities() + ($filters['city'] ? [$filters['city'] => $city] : [])),
            'cityName' => $city,
            'areaName' => $areaName,
            'developerOptions' => $developers->filter(fn ($d) => $catalog->contains('developer', $d['slug']))->sortBy('name'),
            'areaOptions' => $this->areaOptions($catalog, $filters['area'], $areaName),
            // Only types and stages with a live project (plus a chosen one, so it still shows as selected)
            'types' => Projects::liveTypes() + ($filters['type'] ? [$filters['type'] => Projects::typeName($filters['type'])] : []),
            'statuses' => Projects::liveStages(),
            'view' => $request->query('view') === 'map' ? 'map' : 'grid',
            // City, area, type and stage lists are pages of their own for search engines (C4); search text, sort,
            // developer and the map/grid switch are not, so they point back to the list without them
            'canonical' => route('projects.index', array_filter([
                'city' => $filters['city'],
                'area' => $filters['area'],
                'type' => $filters['type'],
                'status' => $filters['status'],
                'page' => $page > 1 ? $page : null,
            ])),
        ]);
    }

    /**
     * Area filter choices, city slug => [area slug => name]: every area with a listed project, in its city's group.
     * An area reached from a link (e.g. a home page location) that isn't a single project area is kept as a choice,
     * so the filter shows what's applied.
     *
     * @param  Collection<int, array<string, mixed>>  $catalog
     * @return array<string, array<string, string>>
     */
    private function areaOptions(Collection $catalog, ?string $current, ?string $currentName): array
    {
        $options = $catalog
            ->groupBy('city')
            ->sortBy(fn ($projects, $city) => Projects::cityName($city))
            ->map(fn ($projects) => $projects->mapWithKeys(fn ($p) => [$p['area_slug'] => $p['area']])->sort()->all())
            ->all();

        if ($current && ! collect($options)->contains(fn ($areas) => isset($areas[$current]))) {
            $options[array_key_first($options) ?? ''][$current] = $currentName ?? $current;
        }

        return $options;
    }

    public function show(string $slug): View
    {
        $project = ProjectCopy::fill(Projects::find($slug) ?? abort(404));
        $developers = Projects::developers()->keyBy('slug');

        return view('pages.projects.show', [
            'project' => $project,
            'status' => Projects::stageName($project['status']),
            'developer' => $developers->get($project['developer']),
            'developers' => $developers,
            'city' => Projects::cityName($project['city']),
            // Same property type the listing filters use (worked out from the project's details)
            'typeName' => Projects::typeName($project['type']),
            'similar' => Projects::similar($project),
            'interests' => StoreEnquiryRequest::INTERESTS,
        ]);
    }
}
