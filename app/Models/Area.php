<?php

namespace App\Models;

use App\Models\Concerns\DeletesReplacedUploads;
use App\Models\Concerns\IsProjectList;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An area within a city. Areas switched on for the home page appear in "Where Pakistan is building next". */
#[Fillable(['city_slug', 'name', 'slug', 'description', 'image', 'show_on_home', 'home_order'])]
class Area extends Model
{
    use DeletesReplacedUploads, IsProjectList;

    const PROJECT_COLUMN = 'area_slug';

    const SLUG_LENGTH = 120;

    /** Contact messages store the chosen area's slug too (C-06), so a renamed address moves them with it (M1) */
    const OTHER_COLUMNS = ['contact_messages' => 'area'];

    /** Fields holding admin uploads; replaced or removed files are deleted (A3) */
    public const UPLOAD_FIELDS = ['image'];

    protected function casts(): array
    {
        return ['show_on_home' => 'boolean'];
    }

    protected static function booted(): void
    {
        // The same name can exist in two cities (DHA): the second one gets the city in its web address
        static::saving(function (Area $area) {
            $taken = fn (string $slug) => static::where('slug', $slug)->whereKeyNot($area->getKey())->exists();
            if ($taken($area->slug)) {
                $base = $area->slug.'-'.$area->city_slug;
                $area->slug = $base;
                for ($n = 2; $taken($area->slug); $n++) {
                    $area->slug = $base.'-'.$n; // rare: e.g. an area literally named "DHA Lahore" in another city (M3)
                }
            }

            // Switched on for the home page: goes last; switched off: leaves the order
            if (! $area->show_on_home) {
                $area->home_order = null;
            } elseif ($area->home_order === null) {
                $area->home_order = (int) static::where('show_on_home', true)->max('home_order') + 1;
            }
        });

        // Projects keep the area name as text too, so the cards stay right after a rename
        static::updated(function (Area $area) {
            if ($area->wasChanged('name')) {
                Project::where('area_slug', $area->slug)->update(['area' => $area->name]);
            }
        });
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_slug', 'slug');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /**
     * Area options for one city, or all areas labelled with their city.
     *
     * @return array<string, string>
     */
    public static function optionsFor(?string $city): array
    {
        return static::query()->when($city, fn ($q) => $q->where('city_slug', $city))->ordered()
            ->get()->mapWithKeys(fn (Area $a) => [$a->slug => $city ? $a->name : $a->name.', '.City::nameFor($a->city_slug)])->all();
    }

    public function usage(): ?string
    {
        return ($n = $this->projects()->count()) ? $n.' '.str('project')->plural($n) : null;
    }
}
