<?php

namespace App\Models;

use App\Models\Concerns\DeletesReplacedUploads;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A builder managed in the admin panel. The public site still reads config/developers.php until the switch-over. */
#[Fillable(['name', 'slug', 'logo', 'is_partner', 'about'])]
class Developer extends Model
{
    use DeletesReplacedUploads;

    /** Fields holding admin uploads; replaced or removed files (and the grey -mono copy) are deleted (A3) */
    public const UPLOAD_FIELDS = ['logo'];

    protected function casts(): array
    {
        return ['is_partner' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Projects point at a developer by slug, so a renamed web address moves its projects with it
        static::updated(function (Developer $developer) {
            if ($developer->wasChanged('slug')) {
                Project::where('developer_slug', $developer->getOriginal('slug'))->update(['developer_slug' => $developer->slug]);
            }
        });
    }

    public function projectCount(): int
    {
        return Project::where('developer_slug', $this->slug)->count();
    }

    /** The grey copy of a logo: same path with "-mono" before the extension. */
    public static function monoVersion(?string $logo): ?string
    {
        return $logo ? preg_replace('/\.webp$/', '-mono.webp', $logo) : null;
    }

    /**
     * Builder options for selects (slug => name), from this table.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return static::query()->orderBy('name')->pluck('name', 'slug')->all();
    }

    public static function nameFor(?string $slug): ?string
    {
        // One lookup per request, however many rows ask; an unknown builder shows its slug, like the other lists (M5)
        return $slug === null ? null : (once(fn () => static::options())[$slug] ?? $slug);
    }
}
