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
        $plain = fn (array $labels) => collect($labels)->values()->map(fn ($label) => ['label' => (string) $label, 'image' => null])->all();

        return match ($this->source) {
            'types' => $plain(Projects::liveTypes()),
            'cities' => $plain(Projects::liveCities()),
            'stages' => $plain(Projects::liveStages()),
            'areas' => once(fn () => Area::query()->whereIn('slug', Projects::all()->pluck('area_slug')->filter()->unique()->values())
                ->orderBy('name')->get()
                ->map(fn (Area $a) => ['label' => $a->name, 'image' => Projects::imageUrl($a->image, 'locations')])
                ->values()->all()),
            default => collect($this->options ?? [])->filter(fn ($o) => filled($o['label'] ?? null))
                ->map(fn ($o) => ['label' => trim($o['label']), 'image' => Projects::imageUrl($o['image'] ?? null, 'finder')])
                ->values()->all(),
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
