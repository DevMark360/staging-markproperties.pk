<?php

namespace App\Models;

use App\Support\FeatureIcons;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/** An amenity or utility. Amenity and Utility are the same table seen through their kind. */
#[Fillable(['name', 'icon', 'sort_order'])]
abstract class Feature extends Model
{
    protected $table = 'features';

    /** amenity | utility */
    const KIND = '';

    protected static function booted(): void
    {
        static::addGlobalScope('kind', fn (Builder $query) => $query->where('features.kind', static::KIND));

        static::creating(function (Feature $feature) {
            $feature->kind = static::KIND;
            $feature->icon ??= FeatureIcons::guess($feature->name);
            $feature->sort_order = $feature->sort_order ?: (int) static::max('sort_order') + 1;
        });
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'feature_project', 'feature_id')->withPivotValue('kind', static::KIND);
    }

    /** Moves every project onto $target, then deletes this one. */
    public function mergeInto(Feature $target): void
    {
        DB::transaction(function () use ($target) {
            $target->projects()->syncWithoutDetaching($this->projects()->pluck('projects.id'));
            $this->delete();
        });
    }
}
