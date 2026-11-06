<?php

namespace App\Models;

use App\Models\Concerns\IsProjectList;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'show_in_menu', 'sort_order'])]
class Stage extends Model
{
    use IsProjectList;

    const PROJECT_COLUMN = 'stage_slug';

    /** The stage the home page's "New launches" row and "All pre-launch" link show (by web address) */
    const PRE_LAUNCH = 'pre-launch';

    /** projects.stage_slug holds 30 characters */
    const SLUG_LENGTH = 30;

    const OTHER_COLUMNS = ['partnership_requests' => 'stage'];

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
