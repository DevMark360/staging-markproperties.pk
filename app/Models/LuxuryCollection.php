<?php

namespace App\Models;

use App\Models\Concerns\DeletesReplacedUploads;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** A hand-picked Luxury collection; its projects are ordered rows in the luxury_collection_project pivot table. */
#[Fillable(['name', 'slug', 'tagline', 'description', 'cover', 'sort_order'])]
class LuxuryCollection extends Model
{
    use DeletesReplacedUploads;

    /** Fields holding admin uploads; replaced or removed files are deleted (A3) */
    public const UPLOAD_FIELDS = ['cover'];

    protected static function booted(): void
    {
        static::saving(function (LuxuryCollection $collection) {
            $collection->slug = Str::slug($collection->slug ?: $collection->name);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(LuxuryCollectionProject::class)->orderBy('sort_order');
    }
}
