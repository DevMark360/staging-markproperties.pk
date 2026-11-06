<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * An upload the image tools can open: a real JPEG, PNG or WebP within the size limit (C8). Checked before saving,
 * so a broken file or an SVG shows a message under the field instead of a server error.
 */
class ReadableImage implements ValidationRule
{
    public const TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return; // an already-saved path, not a new upload
        }

        $info = @getimagesize($value->getRealPath());

        if (! $info || ! in_array($info['mime'], self::TYPES, true)) {
            $fail('Please upload a JPG, PNG or WebP image.');
        } elseif (config('images.max_pixels') < $info[0] * $info[1]) {
            $fail('This image is too large. Please upload one under '.round(config('images.max_pixels') / 1_000_000).' megapixels.');
        }
    }
}
