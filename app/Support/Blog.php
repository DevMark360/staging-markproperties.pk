<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** The public blog, read from the admin's Blog posts (only the live ones: published, and not scheduled for later). */
class Blog
{
    /** All live posts, newest first, as arrays (title, slug, category, category_slug, excerpt, date, minutes, image…). */
    public static function all(): Collection
    {
        return once(fn () => BlogPost::query()->live()->with('category')
            ->orderByDesc('published_at')->orderByDesc('id')
            ->get()
            ->map(fn (BlogPost $post) => static::shape($post)));
    }

    /**
     * Category slug => label, in the order they first appear (newest post first). Built from the live posts, so a
     * category with no post is never offered and no filter leads to an empty page.
     */
    public static function categories(): Collection
    {
        return self::all()->whereNotNull('category_slug')->pluck('category', 'category_slug')->unique();
    }

    /** One live post with its article body and table of contents, or null. */
    public static function find(string $slug): ?array
    {
        $post = BlogPost::query()->live()->with('category')->where('slug', $slug)->first();
        if (! $post) {
            return null;
        }

        $body = self::anchorHeadings((string) $post->body);

        return static::shape($post) + ['body' => $body, 'toc' => self::toc($body)];
    }

    private static function shape(BlogPost $post): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'category' => $post->category?->name,
            'category_slug' => $post->category?->slug,
            'excerpt' => (string) $post->excerpt,
            'date' => $post->published_at,
            'minutes' => $post->read_minutes,
            // Card size for lists, the large copy for the article page; the site's default picture when there is no cover
            'image' => Projects::fileUrl($post->cover_image, 'images/blog') ?? asset('images/og-default.jpg'),
            'image_large' => Projects::fileUrl(Project::largeVersion($post->cover_image), 'images/blog') ?? asset('images/og-default.jpg'),
        ];
    }

    /**
     * Gives every h2 an anchor id from its text (a repeated heading gets -2, -3…), so each contents link has its own
     * target (N1). Headings that already have an id keep it.
     */
    public static function anchorHeadings(string $html): string
    {
        $used = [];

        return trim(preg_replace_callback('#<h2>(.+?)</h2>#s', function ($m) use (&$used) {
            $id = Str::slug(strip_tags($m[1])) ?: 'section';
            $used[$id] = ($used[$id] ?? 0) + 1;

            return '<h2 id="'.($used[$id] > 1 ? $id.'-'.$used[$id] : $id).'">'.$m[1].'</h2>';
        }, $html) ?? $html);
    }

    /** Anchor id => heading text for every h2 with an id. */
    public static function toc(string $html): array
    {
        preg_match_all('#<h2 id="([^"]+)"[^>]*>(.+?)</h2>#s', $html, $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn ($m) => [$m[1] => html_entity_decode(strip_tags($m[2]))])->all();
    }
}
