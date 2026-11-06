<?php

namespace App\Http\Controllers;

use App\Support\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        $catalog = Projects::catalog();
        $developers = $this->developers()
            // Only builders with live projects, partners first then by portfolio size
            ->filter(fn (array $d) => $catalog->contains('developer', $d['slug']))
            ->sortByDesc(fn (array $d) => [$d['partner'], $catalog->where('developer', $d['slug'])->count()])
            ->map(fn (array $d) => [
                ...$d,
                'cities' => $catalog->where('developer', $d['slug'])->pluck('city')->unique()
                    ->map(fn ($city) => Projects::cityName($city))->values()->all(),
            ])
            ->values();

        return view('pages.developers.index', [
            'partners' => $developers->where('partner', true)->values(),
            'others' => $developers->where('partner', false)->values(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $developer = $this->developers()->firstWhere('slug', $slug) ?? abort(404);
        $projects = Projects::catalog()->where('developer', $slug)->values();
        // A builder with no live projects has no page (P1): it would be empty, with a broken description
        abort_if($projects->isEmpty(), 404);

        return view('pages.developers.show', [
            'developer' => $developer,
            'projects' => $projects,
            'cover' => $projects->first(fn ($p) => $p['hero']) ?? $projects->first(fn ($p) => $p['image']),
            'stages' => collect(Projects::stages())->filter(fn ($name, $slug) => $projects->contains('status', $slug)),
            'cityNames' => $projects->pluck('city')->unique()->map(fn ($c) => Projects::cityName($c))->values(),
            'areas' => $projects->pluck('area')->unique()->sort()->values(),
            'types' => $projects->pluck('type')->unique()->filter()->map(fn ($t) => Projects::typeName($t))->values(),
            'points' => Projects::mapPoints($projects),
            'view' => $request->query('view') === 'map' ? 'map' : 'grid',
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function developers(): Collection
    {
        return Projects::developers();
    }
}
