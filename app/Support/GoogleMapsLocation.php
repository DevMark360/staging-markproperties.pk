<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Reads a place's coordinates from what an admin copies out of Google Maps: the "Embed a map" <iframe> code, a
 * place or map link, or a short share link (maps.app.goo.gl, followed to the full link).
 */
class GoogleMapsLocation
{
    /**
     * The address of Google's "Embed a map" frame (https://www.google.com/maps/embed?pb=...), from the <iframe> code
     * or the address itself; null for anything else, so no other site or HTML can be put on the page.
     */
    public static function embedUrl(string $text): ?string
    {
        $text = html_entity_decode(trim($text), ENT_QUOTES);
        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $text, $m)) {
            $text = $m[1];
        }
        $text = trim($text);

        return preg_match('~^https://(?:www\.)?google\.com/maps/embed\?pb=[A-Za-z0-9!._%:+\-]+(?:&[A-Za-z0-9=_%.\-]*)*$~', $text) ? $text : null;
    }

    /** @return array{0: float, 1: float}|null [latitude, longitude] */
    public static function coordinates(string $text): ?array
    {
        $text = html_entity_decode(trim($text), ENT_QUOTES);

        // Just the src of an <iframe>
        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $text, $m)) {
            $text = $m[1];
        }
        $text = rawurldecode($text);

        // A short share link: follow it to the full Google Maps link
        if (preg_match('~https?://(?:maps\.app\.goo\.gl|goo\.gl/maps)/\S+~i', $text, $m)) {
            try {
                $response = Http::timeout(8)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($m[0]);
                $text = rawurldecode((string) $response->effectiveUri());
            } catch (Throwable) {
                return null;
            }
        }

        $patterns = [
            '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/' => [1, 2], // place link: the pin itself
            '/!2d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)/' => [2, 1], // embed code: the place it centres on
            '/[?&](?:q|query|ll|center|destination)=(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/' => [1, 2],
            '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/' => [1, 2], // map view
            '/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/' => [1, 2], // "24.86, 67.00"
        ];
        foreach ($patterns as $pattern => [$lat, $lng]) {
            if (preg_match($pattern, $text, $m)) {
                // 7 decimal places is about 1 cm; it also drops float noise like 24.860734299999997
                $point = [round((float) $m[$lat], 7), round((float) $m[$lng], 7)];
                if (abs($point[0]) <= 90 && abs($point[1]) <= 180 && $point !== [0.0, 0.0]) {
                    return $point;
                }
            }
        }

        return null;
    }
}
