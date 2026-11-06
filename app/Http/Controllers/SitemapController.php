<?php

namespace App\Http\Controllers;

use App\Support\Blog;
use App\Support\Projects;
use Illuminate\Http\Response;

/** sitemap.xml and robots.txt, so search engines find every page (C5). */
class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $catalog = Projects::catalog();

        $urls = collect([
            ...array_map(fn (string $route) => ['loc' => route($route)], [
                'home', 'projects.index', 'luxe', 'developers.index', 'partner', 'guide', 'blog', 'faqs', 'about', 'contact', 'privacy', 'terms',
            ]),
            // City lists are pages of their own (they keep their address as canonical)
            ...$catalog->pluck('city')->unique()->map(fn (string $city) => ['loc' => route('projects.index', ['city' => $city])]),
            ...$catalog->map(fn (array $project) => ['loc' => route('projects.show', $project['slug'])]),
            // Only builders with live projects have a page
            ...Projects::developers()->filter(fn (array $d) => $catalog->contains('developer', $d['slug']))
                ->map(fn (array $d) => ['loc' => route('developers.show', $d['slug'])]),
            ...collect(Projects::liveLuxuryCollections())->map(fn (string $slug) => ['loc' => route('luxe', ['collection' => $slug])]),
            ...Blog::all()->map(fn (array $post) => ['loc' => route('blog.show', $post['slug']), 'lastmod' => $post['date']->toDateString()]),
        ])->unique('loc')->values();

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
