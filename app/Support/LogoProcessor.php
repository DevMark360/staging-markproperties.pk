<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Stores an uploaded builder logo as WebP (max 600px wide) plus its grey copy ("-mono"): the logo's plain
 * background made transparent and every remaining pixel painted ink-grey. The site uses the grey copy as a
 * CSS mask and fades to the colour logo on hover.
 */
class LogoProcessor
{
    private const MAX_WIDTH = 600;

    private const INK = [20, 20, 20];

    /** Colour distance below which a pixel counts as background, and the soft edge above it */
    private const BG_DISTANCE = 38;

    private const EDGE = 40;

    /** @return array{logo: string, mono: string} paths on the images disk */
    public static function store(UploadedFile|string $file, string $name, string $folder = 'uploads/developers'): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        // Same checks and messages as ImageResizer (I5)
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
        $image = self::fit($image);

        $base = trim($folder, '/').'/'.(Str::slug($name) ?: 'logo').'-'.Str::lower(Str::random(6));
        $disk = Storage::disk(config('images.disk'));
        $disk->put("{$base}.webp", self::webp($image));
        $disk->put("{$base}-mono.webp", self::webp(self::mono($image)));

        return ['logo' => "{$base}.webp", 'mono' => "{$base}-mono.webp"];
    }

    private static function fit(GdImage $image): GdImage
    {
        imagepalettetotruecolor($image);
        [$w, $h] = [imagesx($image), imagesy($image)];
        $width = min($w, self::MAX_WIDTH);
        $height = max(1, (int) round($h * $width / $w));

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, $w, $h);

        return $canvas;
    }

    /** Transparent background (detected from the corners), logo pixels in ink grey. */
    private static function mono(GdImage $image): GdImage
    {
        [$w, $h] = [imagesx($image), imagesy($image)];
        $backgrounds = self::backgroundColours($image);

        $out = imagecreatetruecolor($w, $h);
        imagealphablending($out, false);
        imagesavealpha($out, true);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                $opacity = 1 - $c['alpha'] / 127; // already-transparent pixels stay transparent

                foreach ($backgrounds as $bg) {
                    $opacity *= max(0, min(1, (self::distance([$c['red'], $c['green'], $c['blue']], $bg) - self::BG_DISTANCE) / self::EDGE));
                }

                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, ...[...self::INK, (int) round(127 * (1 - $opacity))]));
            }
        }

        return self::trim($out);
    }

    /** Crops the empty (transparent) border, keeping a small margin, so the grey logo fills its tile. */
    private static function trim(GdImage $image): GdImage
    {
        [$w, $h] = [imagesx($image), imagesy($image)];
        [$left, $top, $right, $bottom] = [$w, $h, -1, -1];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ((imagecolorat($image, $x, $y) >> 24) < 115) { // visible pixel
                    [$left, $top, $right, $bottom] = [min($left, $x), min($top, $y), max($right, $x), max($bottom, $y)];
                }
            }
        }
        if ($right < 0) {
            return $image; // nothing visible: keep as is
        }

        $pad = (int) round(max($right - $left, $bottom - $top) * 0.04);
        $rect = ['x' => max(0, $left - $pad), 'y' => max(0, $top - $pad)];
        $rect['width'] = min($w, $right + $pad + 1) - $rect['x'];
        $rect['height'] = min($h, $bottom + $pad + 1) - $rect['y'];

        $cropped = imagecrop($image, $rect);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);

        return $cropped;
    }

    /**
     * Background colours to remove: the corner colour (unless the corners are already transparent), plus an
     * inner "box" colour when the logo sits on a coloured panel inside a margin (e.g. a navy square on white).
     *
     * @return array<int, array{int, int, int}>
     */
    private static function backgroundColours(GdImage $image): array
    {
        [$w, $h] = [imagesx($image), imagesy($image)];
        $colours = [];

        $corner = self::sharedColour($image, [[0, 0], [$w - 1, 0], [0, $h - 1], [$w - 1, $h - 1], [intdiv($w, 2), 0], [intdiv($w, 2), $h - 1]]);
        if ($corner) {
            $colours[] = $corner;
        }

        // Look 12% in from each edge; a uniform colour there that covers much of the logo is a background panel
        [$ix, $iy] = [(int) ($w * 0.12), (int) ($h * 0.12)];
        $inset = self::sharedColour($image, [[$ix, $iy], [$w - 1 - $ix, $iy], [$ix, $h - 1 - $iy], [$w - 1 - $ix, $h - 1 - $iy]]);
        if ($inset && (! $corner || self::distance($inset, $corner) > self::BG_DISTANCE) && self::coverage($image, $inset) > 0.35) {
            $colours[] = $inset;
        }

        return $colours;
    }

    /** The colour all points agree on (within the background tolerance), or null if they differ or are transparent. */
    private static function sharedColour(GdImage $image, array $points): ?array
    {
        $samples = [];
        foreach ($points as [$x, $y]) {
            $c = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            if ($c['alpha'] > 100) {
                return null;
            }
            $samples[] = [$c['red'], $c['green'], $c['blue']];
        }

        // Median of each channel, so one odd point (e.g. part of the logo) does not skew it
        $median = array_map(function ($channel) use ($samples) {
            $values = array_column($samples, $channel);
            sort($values);

            return $values[intdiv(count($values), 2)];
        }, [0, 1, 2]);

        $agreeing = count(array_filter($samples, fn ($c) => self::distance($c, $median) <= self::BG_DISTANCE));

        return $agreeing >= count($samples) - 1 ? $median : null;
    }

    /** Share of the image (sampled on a grid) close to $colour. */
    private static function coverage(GdImage $image, array $colour): float
    {
        [$w, $h] = [imagesx($image), imagesy($image)];
        [$hits, $total] = [0, 0];
        for ($y = 0; $y < $h; $y += max(1, intdiv($h, 40))) {
            for ($x = 0; $x < $w; $x += max(1, intdiv($w, 40))) {
                $c = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                $hits += self::distance([$c['red'], $c['green'], $c['blue']], $colour) <= self::BG_DISTANCE ? 1 : 0;
                $total++;
            }
        }

        return $total ? $hits / $total : 0;
    }

    private static function distance(array $a, array $b): float
    {
        return sqrt(($a[0] - $b[0]) ** 2 + ($a[1] - $b[1]) ** 2 + ($a[2] - $b[2]) ** 2);
    }

    private static function webp(GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, 85);

        return ob_get_clean();
    }
}
