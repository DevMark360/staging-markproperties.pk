<?php

namespace App\Models\Concerns;

use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A list projects point at by slug (cities, areas, property types, stages).
 * The model sets PROJECT_COLUMN, plus OTHER_COLUMNS (table => column) for other rows that store the same slug.
 */
trait IsProjectList
{
    protected static function bootIsProjectList(): void
    {
        static::saving(function (self $item) {
            // Cut to the column's length, so an address filled from a long name can't fail in the database (A1)
            $item->slug = rtrim(mb_substr(Str::slug($item->slug ?: $item->name), 0, static::slugLength()), '-');
        });

        // A renamed web address moves everything that points at it
        static::updated(function (self $item) {
            if (! $item->wasChanged('slug')) {
                return;
            }
            foreach (['projects' => static::PROJECT_COLUMN] + (defined(static::class.'::OTHER_COLUMNS') ? static::OTHER_COLUMNS : []) as $table => $column) {
                DB::table($table)->where($column, $item->getOriginal('slug'))->update([$column => $item->slug]);
            }
        });
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, static::PROJECT_COLUMN, 'slug');
    }

    /**
     * Options for selects (slug => name), in the admin's order.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return static::query()->ordered()->pluck('name', 'slug')->all();
    }

    /** Longest web address the table (and the project column pointing at it) can hold; a model sets SLUG_LENGTH to change it. */
    public static function slugLength(): int
    {
        return defined(static::class.'::SLUG_LENGTH') ? static::SLUG_LENGTH : 40;
    }

    public static function nameFor(?string $slug): ?string
    {
        return $slug === null ? null : (once(fn () => static::options())[$slug] ?? $slug);
    }
}
