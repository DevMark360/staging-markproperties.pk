<?php

namespace App\Models;

use App\Models\Concerns\IsProjectList;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'show_in_menu', 'sort_order'])]
class PropertyType extends Model
{
    use IsProjectList;

    const PROJECT_COLUMN = 'type_slug';

    const OTHER_COLUMNS = ['partnership_requests' => 'type'];

    protected function casts(): array
    {
        return ['show_in_menu' => 'boolean'];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function usage(): ?string
    {
        return ($n = $this->projects()->count()) ? $n.' '.str('project')->plural($n) : null;
    }
}
