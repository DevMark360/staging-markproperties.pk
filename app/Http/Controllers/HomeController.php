<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Project;
use App\Support\Blog;
use App\Support\Projects;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $projects = Projects::all();
        $projectCounts = $projects->countBy('developer');
        $developers = Projects::developers()
            ->map(fn (array $developer) => [...$developer, 'projects' => $projectCounts[$developer['slug']] ?? 0])
            ->keyBy('slug');

        return view('pages.home', [
            // Only projects switched on in the admin ("Show in the home page slider" / "Show in Trending"); none on = none shown
            'featured' => $this->section('home'),
            'launches' => $projects->where('status', Stage::PRE_LAUNCH)->values(), // newest first
            'trending' => $this->section('trending'),
            'developers' => $developers,
            // Only stages and cities with a live project (search tabs, city lists)
            'statuses' => Projects::liveStages(),
            'cities' => Projects::liveCities(),
            'searchHints' => $projects->pluck('name')->values(),
            'faqs' => array_slice(config('faqs'), 0, 5), // the first five on Home (shown and in its FAQ schema); the rest are on /faqs
            'posts' => Blog::all()->take(3), // newest first, same shape as the blog page (post-card)
            // "Where Pakistan is building next": every area switched on for the home page; those without a published
            // project show but aren't links (pages/home.blade.php)
            'locations' => Projects::homeAreas(),
        ]);
    }

    /**
     * Projects switched on in the admin for a home section, in their set order (published ones only). Empty when
     * none are switched on, so the section shows nothing rather than other projects.
     */
    private function section(string $section): Collection
    {
        $live = Projects::all()->keyBy('slug');

        return Project::inSection($section)
            ->where('is_published', true)
            ->whereIn('slug', $live->keys())
            ->limit(Project::sectionLimit($section))
            ->get()
            ->map(fn (Project $project) => $project->toCard() + ['views' => $live[$project->slug]['views'] ?? 0])
            ->values();
    }
}
