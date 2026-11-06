<?php

namespace App\Http\Controllers;

use App\Support\Projects;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LuxeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $catalog = Projects::catalog()->keyBy('slug');

        $collections = Projects::luxuryCollections()->map(fn (array $collection) => [
            ...$collection,
            'projects' => collect($collection['projects'])
                ->map(fn (string $slug) => $catalog->get($slug))
                ->filter()
                ->map(fn (array $p) => [...$p, 'photo' => static::photo($p)])
                ->values(),
        ])->filter(fn (array $collection) => $collection['projects']->isNotEmpty())->values();

        $active = $collections->firstWhere('slug', $request->query('collection'));

        return view('pages.luxe', [
            'collections' => $collections,
            'active' => $active,
            'shown' => $active ? collect([$active]) : $collections,
            'developers' => Projects::developers()->keyBy('slug'),
        ]);
    }

    private static function photo(array $project): ?string
    {
        // Card-sized copies (800px wide) made for the tall Luxe cards; see public/images/luxe
        $card = "images/luxe/{$project['slug']}.webp";

        return match (true) {
            is_file(public_path($card)) => asset($card),
            (bool) $project['image'] => Projects::imageUrl($project['image']),
            default => null,
        };
    }
}
