<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Removes admin-uploaded files that are no longer used (A3): the old file when an image or brochure is replaced or
 * removed, and all of them when the record is deleted. Every size the image tools made goes too (-800, -1600,
 * -2400, and a logo's -mono copy). Only files under uploads/ are touched, never the site's own images/ files.
 *
 * The model lists its upload fields in UPLOAD_FIELDS (a field may hold one path or a list, e.g. a gallery).
 */
trait DeletesReplacedUploads
{
    protected static function bootDeletesReplacedUploads(): void
    {
        static::updated(function (self $model) {
            $before = $model->uploadedFiles(fn (string $field) => $model->getOriginal($field));
            $after = $model->uploadedFiles(fn (string $field) => $model->{$field});
            self::deleteUploads(array_diff($before, $after));
        });

        static::deleted(fn (self $model) => self::deleteUploads($model->uploadedFiles(fn (string $field) => $model->{$field})));
    }

    /** @return list<string> every stored file behind the upload fields, all sizes included */
    private function uploadedFiles(callable $value): array
    {
        $files = [];
        foreach (static::UPLOAD_FIELDS as $field) {
            foreach ((array) $value($field) as $path) {
                if (! is_string($path) || ! str_starts_with($path, 'uploads/')) {
                    continue;
                }
                $files[] = $path;
                // The other sizes made from the same upload: name-800.webp ↔ name-1600.webp / name-2400.webp
                if (preg_match('/^(.*)-(\d+)\.webp$/', $path, $m)) {
                    foreach (config('images.sizes') as $width) {
                        $files[] = "{$m[1]}-{$width}.webp";
                    }
                }
                // A builder logo's grey copy
                if (str_ends_with($path, '.webp')) {
                    $files[] = substr($path, 0, -5).'-mono.webp';
                }
            }
        }

        return array_values(array_unique($files));
    }

    /** @param  array<int, string>  $files */
    private static function deleteUploads(array $files): void
    {
        if ($files) {
            Storage::disk(config('images.disk'))->delete(array_values($files));
        }
    }
}
