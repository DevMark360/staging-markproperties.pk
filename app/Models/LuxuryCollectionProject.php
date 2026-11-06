<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One project in a Luxury collection, with its place in the order (the luxury_collection_project pivot table). */
#[Fillable(['luxury_collection_id', 'project_id', 'sort_order'])]
class LuxuryCollectionProject extends Model
{
    protected $table = 'luxury_collection_project';

    public $timestamps = false;

    public function collection(): BelongsTo
    {
        return $this->belongsTo(LuxuryCollection::class, 'luxury_collection_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
