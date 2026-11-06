<?php

namespace App\Models;

use App\Models\Concerns\DeletesReplacedUploads;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A blog article managed in the admin panel; the public blog shows the live ones (App\Support\Blog). */
#[Fillable(['title', 'slug', 'blog_category_id', 'excerpt', 'body', 'cover_image', 'status', 'published_at'])]
class BlogPost extends Model
{
    use DeletesReplacedUploads;

    /** Fields holding admin uploads; replaced or removed files are deleted (A3) */
    public const UPLOAD_FIELDS = ['cover_image'];

    public const STATUSES = ['draft' => 'Draft', 'published' => 'Published'];

    private const WORDS_PER_MINUTE = 200;

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            $post->read_minutes = static::readMinutes($post->body);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /** Live on the site: published and not scheduled for later. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Draft, Scheduled (published with a future date) or Published. */
    public function stateLabel(): string
    {
        return match (true) {
            $this->status !== 'published' => 'Draft',
            $this->published_at === null || $this->published_at->isFuture() => 'Scheduled',
            default => 'Published',
        };
    }

    public static function readMinutes(?string $html): int
    {
        // Unicode-aware, so Urdu (and any other script) is counted too (M6)
        $words = preg_match_all('/[\p{L}\p{N}]+/u', strip_tags(str_replace('>', '> ', (string) $html)));

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
