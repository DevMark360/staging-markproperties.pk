<?php

namespace App\Models;

use App\Support\Projects;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A question in the home page property finder pop-up. */
#[Fillable(['title', 'hint', 'source', 'multiple', 'options', 'sort_order', 'is_active'])]
class FinderQuestion extends Model
{
    /** Where a question's options come from. The lists offer only entries with at least one live project. */
    public const SOURCES = [
        'custom' => 'My own options',
        'types' => 'Property types',
        'cities' => 'Cities',
        'areas' => 'Areas',
        'stages' => 'Stages',
    ];

    protected function casts(): array
    {
        return ['multiple' => 'boolean', 'is_active' => 'boolean', 'options' => 'array'];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    /**
     * What the visitor can pick: a label and a photo URL (or null). Empty when the list has nothing live; the
     * question is then skipped.
     *
     * @return list<array{label: string, image: ?string}>
     */
    public function choices(): array
    {
        if ($this->source === 'custom' || ! array_key_exists($this->source, self::SOURCES)) {
            return collect($this->options ?? [])->filter(fn ($o) => filled($o['label'] ?? null))
                ->map(fn ($o) => ['label' => trim($o['label']), 'image' => Projects::imageUrl($o['image'] ?? null, 'finder')])
                ->values()->all();
        }

        // A list: its live entries (slug => name), with the photo given here, or the area's own photo
        $photos = collect($this->options ?? [])->filter(fn ($o) => filled($o['key'] ?? null) && filled($o['image'] ?? null))
            ->mapWithKeys(fn ($o) => [$o['key'] => Projects::imageUrl($o['image'], 'finder')]);
        $own = $this->source === 'areas' ? self::liveAreas()->map(fn (Area $a) => Projects::imageUrl($a->image, 'locations')) : collect();

        return collect(self::liveList($this->source))
            ->map(fn ($name, $slug) => ['label' => (string) $name, 'image' => $photos[$slug] ?? $own[$slug] ?? null])
            ->values()->all();
    }

    /** @return array<string, string> a list's live entries, slug => name */
    private static function liveList(string $source): array
    {
        return match ($source) {
            'types' => Projects::liveTypes(),
            'cities' => Projects::liveCities(),
            'stages' => Projects::liveStages(),
            'areas' => self::liveAreas()->map(fn (Area $a) => $a->name)->all(),
            default => [],
        };
    }

    /** @return \Illuminate\Support\Collection<string, Area> areas with at least one live project, by slug, A to Z */
    private static function liveAreas(): \Illuminate\Support\Collection
    {
        return once(fn () => Area::query()->whereIn('slug', Projects::all()->pluck('area_slug')->filter()->unique()->values())
            ->orderBy('name')->get()->keyBy('slug'));
    }

    /**
     * Every entry of a list (live or not) for the admin's photo picker, slug => name.
     *
     * @return array<string, string>
     */
    public static function listOptions(?string $source): array
    {
        return match ($source) {
            'types' => Projects::types(),
            'cities' => Projects::cities(),
            'stages' => Projects::stages(),
            'areas' => Area::query()->orderBy('name')->pluck('name', 'slug')->all(),
            default => [],
        };
    }

    /**
     * The questions the pop-up asks, in order, each with its choices; a question with nothing to pick is left out.
     *
     * @return list<array{id: int, title: string, hint: ?string, multiple: bool, choices: list<array{label: string, image: ?string}>}>
     */
    public static function forPopup(): array
    {
        return self::query()->where('is_active', true)->ordered()->get()
            ->map(fn (self $q) => ['id' => $q->id, 'title' => $q->title, 'hint' => $q->hint, 'multiple' => $q->multiple, 'choices' => $q->choices()])
            ->filter(fn ($q) => $q['choices'])
            ->values()->all();
    }
}
