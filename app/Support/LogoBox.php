<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Optical sizing for builder logos. Logos come in every shape (wide wordmarks, tall crests, thin line art),
 * so fitting them all into the same box makes some look huge and others tiny or faint. Instead each logo gets
 * the same visual area: wide logos are made shorter, tall ones taller, and very light line art slightly
 * bigger (capped), measured from the logo's grey "-mono" copy.
 */
class LogoBox
{
    /** Ink coverage the boost is relative to (a typical solid logo) and the limits of the boost */
    private const TYPICAL_COVERAGE = 0.35;

    private const BOOST = [0.9, 1.3];

    /**
     * Width and height as multiples of a square side (1 = a square logo of typical weight).
     *
     * @return array{w: float, h: float}
     */
    public static function for(string $publicPath): array
    {
        $file = public_path($publicPath);
        if (! is_file($file)) {
            return ['w' => 1.0, 'h' => 1.0];
        }

        return Cache::rememberForever('logo-box:'.md5($publicPath.filemtime($file)), fn () => self::measure($file));
    }

    /** @return array{w: float, h: float} */
    private static function measure(string $file): array
    {
        $image = @imagecreatefromstring((string) file_get_contents($file));
        if (! $image) {
            return ['w' => 1.0, 'h' => 1.0];
        }

        [$w, $h] = [imagesx($image), imagesy($image)];
        [$minX, $minY, $maxX, $maxY, $ink] = [$w, $h, -1, -1, 0];
        // Sample every 2nd pixel: plenty for a ratio and a coverage figure
        for ($y = 0; $y < $h; $y += 2) {
            for ($x = 0; $x < $w; $x += 2) {
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) < 100) { // mostly opaque = ink
                    $ink++;
                    [$minX, $minY, $maxX, $maxY] = [min($minX, $x), min($minY, $y), max($maxX, $x), max($maxY, $y)];
                }
            }
        }

        if ($maxX < 0) {
            return ['w' => 1.0, 'h' => 1.0];
        }

        $boxW = $maxX - $minX + 2;
        $boxH = $maxY - $minY + 2;
        $ratio = $boxW / $boxH;
        $coverage = $ink * 4 / ($boxW * $boxH);
        $boost = min(self::BOOST[1], max(self::BOOST[0], ($coverage / self::TYPICAL_COVERAGE) ** -0.25));

        return ['w' => round(sqrt($ratio) * $boost, 3), 'h' => round($boost / sqrt($ratio), 3)];
    }
}
