<?php

namespace App\Models;

use App\Models\Concerns\IsProjectList;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'sort_order'])]
class City extends Model
{
    use IsProjectList;

    const PROJECT_COLUMN = 'city_slug';

    const OTHER_COLUMNS = ['areas' => 'city_slug', 'contact_messages' => 'city', 'launch_alerts' => 'city', 'partnership_requests' => 'city'];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'city_slug', 'slug');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /** Cities still used by projects, areas or leads cannot be deleted. */
    public function usage(): ?string
    {
        $parts = array_filter([
            ($n = $this->projects()->count()) ? $n.' '.str('project')->plural($n) : null,
            ($n = $this->areas()->count()) ? $n.' '.str('area')->plural($n) : null,
        ]);

        return $parts ? implode(' and ', $parts) : null;
    }
}
