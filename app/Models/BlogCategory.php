<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug'])]
class BlogCategory extends Model
{
    protected static function booted(): void
    {
        static::saving(function (BlogCategory $category) {
            $category->slug = Str::slug($category->slug ?: $category->name);
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
