<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Resizes an uploaded image with GD and stores it as WebP at the sizes in config/images.php
 * (the default sizes, or the ones the caller names).
 *
 * Usage (e.g. from a Filament FileUpload later):
 *   $paths = ImageResizer::store($file, 'projects', 'falaknaz-skyview-towers');
 *   // ['card' => 'projects/falaknaz-skyview-towers-800.webp', 'large' => 'projects/falaknaz-skyview-towers-1600.webp']
 */
class ImageResizer
{
    /**
     * @param  list<string>|null  $sizes  size names from config('images.sizes'); null = config('images.default_sizes')
     * @return array<string, string> size name => path on the images disk
     */
    public static function store(UploadedFile|string $file, string $folder, ?string $name = null, ?array $sizes = null): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $name = Str::slug($name ?? pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : $path, PATHINFO_FILENAME)) ?: Str::random(12);

        // A 12-megapixel phone photo needs ~50 MB once decoded; give GD room, then restore the limit
        // Each photo gets its own time budget, so a save with many gallery photos doesn't hit PHP's limit halfway (A4)
        // Only when a limit is set (web requests): 0 means unlimited (command line, queues), which must stay that way,
        // or a long import or test run would be cut off after 60 seconds
        if (function_exists('set_time_limit') && (int) ini_get('max_execution_time') > 0) {
            @set_time_limit(60);
        }

        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit !== '-1') {
            ini_set('memory_limit', config('images.memory_limit'));
        }

        try {
            return self::resizeAll(self::load($path), $folder, $name, $sizes ?? config('images.default_sizes'));
        } finally {
            ini_set('memory_limit', $memoryLimit);
        }
    }

    /**
     * @param  list<string>  $sizes
     * @return array<string, string>
     */
    private static function resizeAll(GdImage $source, string $folder, string $name, array $sizes): array
    {
        $disk = Storage::disk(config('images.disk'));
        $paths = [];

        foreach (array_intersect_key(config('images.sizes'), array_flip($sizes)) as $size => $width) {
            $resized = self::fitWidth($source, $width);

            ob_start();
            imagewebp($resized, null, config('images.quality'));
            $target = trim($folder, '/')."/{$name}-{$width}.webp";
            $disk->put($target, ob_get_clean());

            unset($resized); // frees the copy (GD images are objects since PHP 8; imagedestroy() is deprecated)
            $paths[$size] = $target;
        }

        unset($source);

        return $paths;
    }

    /** Decodes JPEG, PNG, WebP, GIF or BMP, upright and with transparency kept. */
    private static function load(string $path): GdImage
    {
        $info = @getimagesize($path);
        if (! $info) {
            throw new InvalidArgumentException('The file is not a supported image.');
        }
        if (config('images.max_pixels') < $info[0] * $info[1]) {
            throw new InvalidArgumentException('The image is too large to process.');
        }

        $image = @imagecreatefromstring(file_get_contents($path));
        if (! $image) {
            throw new InvalidArgumentException('The image could not be read.');
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if ($info[2] !== IMAGETYPE_JPEG) {
            return $image;
        }

        // A damaged EXIF block just means "no rotation" (I4), not a failed upload
        try {
            $orientation = self::jpegOrientation($path);
        } catch (\Throwable) {
            $orientation = 1;
        }

        return self::orient($image, $orientation);
    }

    /** Scales down to $width (never up), keeping the aspect ratio and transparency. */
    private static function fitWidth(GdImage $image, int $width): GdImage
    {
        [$w, $h] = [imagesx($image), imagesy($image)];
        if ($w <= $width) {
            return $image;
        }

        $height = max(1, (int) round($h * $width / $w));
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, $w, $h);

        return $canvas;
    }

    /** Rotates/flips a phone photo so it is the right way up (EXIF orientation 1–8). */
    private static function orient(GdImage $image, int $orientation): GdImage
    {
        $rotate = [3 => 180, 4 => 180, 5 => 270, 6 => 270, 7 => 90, 8 => 90][$orientation] ?? 0;
        if ($rotate) {
            $image = imagerotate($image, $rotate, 0);
        }
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        return $image;
    }

    /**
     * Reads the EXIF orientation tag straight from the JPEG bytes, so the PHP exif extension
     * (not installed here, and often missing on shared hosting) is not needed.
     */
    private static function jpegOrientation(string $path): int
    {
        $data = file_get_contents($path, false, null, 0, 128 * 1024);
        $offset = 2;

        while ($offset + 4 <= strlen($data) && $data[$offset] === "\xFF") {
            $marker = ord($data[$offset + 1]);
            $length = unpack('n', substr($data, $offset + 2, 2))[1];

            if ($marker === 0xE1 && substr($data, $offset + 4, 6) === "Exif\0\0") {
                $tiff = $offset + 10;
                $le = substr($data, $tiff, 2) === 'II';
                $u16 = fn (int $at) => unpack($le ? 'v' : 'n', substr($data, $at, 2))[1];
                $u32 = fn (int $at) => unpack($le ? 'V' : 'N', substr($data, $at, 4))[1];

                $ifd = $tiff + $u32($tiff + 4);
                for ($i = 0, $entries = $u16($ifd); $i < $entries; $i++) {
                    $entry = $ifd + 2 + $i * 12;
                    if ($u16($entry) === 0x0112) {
                        return $u16($entry + 8);
                    }
                }

                return 1;
            }

            if ($marker === 0xDA) {
                break; // image data starts; no EXIF block before it
            }
            $offset += 2 + $length;
        }

        return 1;
    }
}
