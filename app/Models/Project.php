<?php

namespace App\Models;

use App\Models\Concerns\DeletesReplacedUploads;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A project managed in the admin panel; the public site shows the published ones (App\Support\Projects).
 * Its builder, stage, city, area and type are stored by slug: developer_slug, stage_slug, city_slug, area_slug, type_slug.
 */
#[Fillable([
    'name', 'slug', 'developer_slug', 'stage_slug', 'city_slug', 'area', 'area_slug', 'address', 'type_slug', 'is_published',
    'show_on_home', 'home_order', 'is_trending', 'trending_order',
    'description', 'highlights', 'amenities', 'utilities',
    'image', 'gallery', 'hero_image', 'latitude', 'longitude', 'map_embed', 'brochure',
])]
class Project extends Model
{
    use DeletesReplacedUploads;

    /** Fields holding admin uploads; replaced or removed files are deleted (A3) */
    public const UPLOAD_FIELDS = ['image', 'gallery', 'hero_image', 'brochure'];

    /** Home page sections: flag column, order column and the config key of its limit. */
    public const SECTIONS = [
        'home' => ['flag' => 'show_on_home', 'order' => 'home_order', 'limit' => 'home.slider_limit', 'label' => 'Home slider'],
        'trending' => ['flag' => 'is_trending', 'order' => 'trending_order', 'limit' => 'home.trending_limit', 'label' => 'Trending'],
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_on_home' => 'boolean',
            'is_trending' => 'boolean',
            'highlights' => 'array',
            'amenities' => 'array',
            'utilities' => 'array',
            'gallery' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        // The area name is kept as text beside its slug, so cards and search need no join
        static::saving(function (Project $project) {
            if ($project->isDirty('area_slug') && $project->area_slug) {
                $project->area = Area::where('slug', $project->area_slug)->value('name') ?? $project->area;
            }
        });
    }

    /** Projects currently in a home section, in display order. */
    public function scopeInSection(Builder $query, string $section): Builder
    {
        $s = self::SECTIONS[$section];

        return $query->where($s['flag'], true)->orderBy($s['order'])->orderBy('id');
    }

    public static function sectionLimit(string $section): int
    {
        return (int) config(self::SECTIONS[$section]['limit']);
    }

    /**
     * Applies the section switches after a save: a project switched on goes to the end (or takes the place of the
     * project it replaces), a project switched off leaves the section.
     *
     * @param  array<string, int|null>  $replacements  section => id of the project it replaces
     * @return array<string, string> section label => name of the project it replaced (for a message to the admin, A9)
     */
    public function syncSections(array $replacements = []): array
    {
        $replacedNames = [];

        foreach (self::SECTIONS as $section => $s) {
            if (! $this->{$s['flag']}) {
                if ($this->{$s['order']} !== null) {
                    $this->forceFill([$s['order'] => null])->saveQuietly();
                    self::renumber($section);
                }

                continue;
            }

            if ($this->{$s['order']} !== null) {
                continue; // already placed
            }

            $replaced = ($id = $replacements[$section] ?? null) ? self::whereKeyNot($this->getKey())->find($id) : null;
            $position = $replaced?->{$s['order']} ?? ((int) self::where($s['flag'], true)->whereKeyNot($this->getKey())->max($s['order']) + 1);
            $replaced?->forceFill([$s['flag'] => false, $s['order'] => null])->saveQuietly();
            if ($replaced) {
                $replacedNames[$s['label']] = $replaced->name;
            }

            $this->forceFill([$s['order'] => $position])->saveQuietly();
        }

        return $replacedNames;
    }

    /** Numbers a section 1, 2, 3… again in its current order, so removing a project leaves no gap (A8). */
    public static function renumber(string $section): void
    {
        $s = self::SECTIONS[$section];
        self::inSection($section)->get()->each(fn (Project $project, int $i) => $project->{$s['order']} === $i + 1
            ? null
            : $project->forceFill([$s['order'] => $i + 1])->saveQuietly());
    }

    /** Amenities from the admin list, in the list's order. */
    public function amenityItems(): BelongsToMany
    {
        return $this->features(Amenity::class);
    }

    public function utilityItems(): BelongsToMany
    {
        return $this->features(Utility::class);
    }

    /** Both kinds share feature_project; the pivot's kind keeps a sync of one from detaching the other. */
    private function features(string $model): BelongsToMany
    {
        return $this->belongsToMany($model, 'feature_project', 'project_id', 'feature_id')
            ->withPivotValue('kind', $model::KIND)
            ->orderBy('features.sort_order');
    }

    public function developerName(): ?string
    {
        return Developer::nameFor($this->developer_slug);
    }

    public function cityName(): ?string
    {
        return City::nameFor($this->city_slug);
    }

    public function stageName(): ?string
    {
        return Stage::nameFor($this->stage_slug);
    }

    public function typeName(): ?string
    {
        return PropertyType::nameFor($this->type_slug);
    }

    /** The project in the array shape the site's views use (same keys as config/projects.php). */
    public function toCard(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'developer' => $this->developer_slug,
            'status' => $this->stage_slug,
            'city' => $this->city_slug,
            'area' => $this->area,
            'type' => $this->type_slug,
            'image' => $this->image,
            // The hero slider needs a wide photo; fall back to the large version of the main image
            'hero' => $this->hero_image ?? self::largeVersion($this->image),
        ];
    }

    /** The 1600px version of an uploaded 800px image (imported images have a single size). */
    public static function largeVersion(?string $path): ?string
    {
        return $path && str_ends_with($path, '-800.webp') ? substr($path, 0, -9).'-1600.webp' : $path;
    }
}
