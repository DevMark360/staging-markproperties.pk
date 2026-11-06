<?php

namespace App\Support;

/** Line icons for amenities and utilities: key => [label, name keywords, SVG paths on a 24px grid]. */
class FeatureIcons
{
    public const FALLBACK = 'check';

    public const ICONS = [
        'gym' => ['Gym', 'gym|fitness', '<path d="M6 8v8M18 8v8M3 10v4M21 10v4M6 12h12" stroke-linecap="round"/>'],
        'pool' => ['Swimming pool', 'pool|swim', '<path d="M3 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0 3-1 4.5 0M3 13c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0 3-1 4.5 0M8 11V5a2 2 0 0 1 4 0M16 11V5a2 2 0 0 0-4 0" stroke-linecap="round"/>'],
        'play' => ['Kids / play area', 'play|kid|child|day-?care', '<circle cx="12" cy="8" r="4"/><path d="M12 12v4m-3 5 3-5 3 5" stroke-linecap="round" stroke-linejoin="round"/>'],
        'mosque' => ['Mosque / prayer', 'mosque|masjid|prayer', '<path d="M5 21V11a7 7 0 0 1 14 0v10M3 21h18M12 4V2M9 21v-4a3 3 0 0 1 6 0v4" stroke-linecap="round"/>'],
        'cctv' => ['CCTV camera', 'cctv|camera|surveillance', '<path d="m3 8 12-3 2 6-12 3zM9 13l1 4H5M17 9l4-1" stroke-linecap="round" stroke-linejoin="round"/>'],
        'security' => ['Security shield', 'security|guard|gated|secure|earthquake|emergency', '<path d="M12 3.5 5 6v5.5c0 4.2 2.9 7.6 7 9 4.1-1.4 7-4.8 7-9V6l-7-2.5Z" stroke-linejoin="round"/><path d="m9 12 2.2 2.2L15.5 10" stroke-linecap="round" stroke-linejoin="round"/>'],
        'parking' => ['Parking', 'parking', '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M10 17V8h3a2.5 2.5 0 0 1 0 5h-3" stroke-linecap="round" stroke-linejoin="round"/>'],
        'lift' => ['Lift / elevator', 'lift|elevator', '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="m9 9 3-3 3 3M9 15l3 3 3-3" stroke-linecap="round" stroke-linejoin="round"/>'],
        'power' => ['Electricity / generator', 'electric|power|generator|backup|solar', '<path d="M13 3 5 14h6l-1 7 8-11h-6l1-7Z" stroke-linejoin="round"/>'],
        'water' => ['Water', 'water|ro plant|boring', '<path d="M12 3s6 6.4 6 11a6 6 0 0 1-12 0c0-4.6 6-11 6-11Z" stroke-linejoin="round"/>'],
        'fire' => ['Fire safety', 'fire', '<path d="M12 3s5 4 5 9a5 5 0 0 1-10 0c0-2 1-3.5 1-3.5s.5 2 2 2.5C10 7 12 3 12 3Z" stroke-linejoin="round"/>'],
        'gas' => ['Gas', 'gas', '<path d="M7 21V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v16M5 21h12M15 9h2a2 2 0 0 1 2 2v5a1.5 1.5 0 0 0 3 0V8l-3-3M9 8h4" stroke-linecap="round" stroke-linejoin="round"/>'],
        'waste' => ['Waste / sewerage', 'sewer|waste|disposal|garbage', '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" stroke-linecap="round" stroke-linejoin="round"/>'],
        'community' => ['Community / hall', 'community|hall|club|lounge|banquet|recreation', '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0M16 5a3 3 0 0 1 0 6M21 20a6 6 0 0 0-4-5.7" stroke-linecap="round"/>'],
        'garden' => ['Garden / park', 'garden|green|landscap|lawn|tree|\bpark\b|bbq', '<path d="M12 21v-7M12 14c-4 0-7-3-7-8 5 0 7 3 7 8Zm0 0c4 0 7-3 7-8-5 0-7 3-7 8Z" stroke-linejoin="round"/>'],
        'internet' => ['Internet / Wi-Fi', 'internet|wi-?fi|broadband', '<path d="M2 9a15 15 0 0 1 20 0M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0" stroke-linecap="round"/><circle cx="12" cy="19.5" r="1"/>'],
        'phone' => ['Intercom / phone', 'intercom|telephone|phone', '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z" stroke-linejoin="round"/>'],
        'school' => ['School / education', 'school|education', '<path d="m2 9 10-5 10 5-10 5-10-5ZM6 11v5c3 2 9 2 12 0v-5" stroke-linejoin="round"/>'],
        'medical' => ['Hospital / medical', 'hospital|medical|clinic|health', '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M12 8v8M8 12h8" stroke-linecap="round"/>'],
        'shop' => ['Shops / commercial', 'mall|shop|retail|commercial|store|market|showroom|mart|salon', '<path d="M5 8h14l-1 12H6L5 8ZM9 8V6a3 3 0 0 1 6 0v2" stroke-linejoin="round"/>'],
        'food' => ['Food / café', 'restaurant|cafe|food|dining', '<path d="M5 3v8a3 3 0 0 0 6 0V3M8 3v18M17 21V3c-2 0-3 3-3 7h3" stroke-linecap="round" stroke-linejoin="round"/>'],
        'cinema' => ['Cinema', 'cinema|theatre|theater', '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="m10 10 4 2.5-4 2.5v-5Z" stroke-linejoin="round"/>'],
        'games' => ['Games', 'games', '<rect x="3" y="8" width="18" height="10" rx="4"/><path d="M8 11v4M6 13h4M15.5 12.5h.01M17.5 14.5h.01" stroke-linecap="round"/>'],
        'track' => ['Jogging track', 'jogging|track|walk', '<circle cx="14" cy="4.5" r="1.5"/><path d="m9 21 2.5-6L14 17v4M7 12l3-3.5 4 1 2 3h3M11.5 15 10 9" stroke-linecap="round" stroke-linejoin="round"/>'],
        'lobby' => ['Lobby / office', 'lobby|reception|office|corridor|quarter', '<path d="M4 21V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16M2 21h20M9 21v-5h6v5M9 7h1M14 7h1M9 11h1M14 11h1" stroke-linecap="round" stroke-linejoin="round"/>'],
        'transport' => ['Transport', 'transport|bus|shuttle', '<rect x="4" y="3" width="16" height="15" rx="3"/><path d="M4 11h16M7 21v-3M17 21v-3M8 14.5h.01M16 14.5h.01" stroke-linecap="round"/>'],
        'storage' => ['Storage / maintenance', 'storage|maintenance|cargo', '<path d="M3 8 12 3l9 5v8l-9 5-9-5V8ZM3 8l9 5 9-5M12 13v8" stroke-linejoin="round"/>'],
        'spa' => ['Spa / steam', 'steam|spa|sauna', '<path d="M8 4c-1 1.5 1 2.5 0 4M12 4c-1 1.5 1 2.5 0 4M16 4c-1 1.5 1 2.5 0 4M4 12h16v2a6 6 0 0 1-6 6h-4a6 6 0 0 1-6-6v-2Z" stroke-linecap="round" stroke-linejoin="round"/>'],
        'check' => ['Check mark (general)', '', '<circle cx="12" cy="12" r="8.5"/><path d="m8.5 12.5 2.2 2.2 4.8-5" stroke-linecap="round" stroke-linejoin="round"/>'],
    ];

    /** The icon whose keywords match the name first. */
    public static function guess(string $name): string
    {
        foreach (self::ICONS as $key => [, $keywords]) {
            if ($keywords !== '' && preg_match('/'.$keywords.'/i', $name)) {
                return $key;
            }
        }

        return self::FALLBACK;
    }

    public static function svg(?string $key, array $attributes = []): string
    {
        $attributes += ['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'aria-hidden' => 'true'];
        $attrs = collect($attributes)->map(fn ($v, $k) => $k.'="'.e($v).'"')->join(' ');

        return '<svg '.$attrs.'>'.(self::ICONS[$key] ?? self::ICONS[self::FALLBACK])[2].'</svg>';
    }

    /**
     * Icon choices for an admin select, each showing the icon beside its label.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::ICONS)->map(fn (array $icon, string $key) => '<span style="display:inline-flex;align-items:center;gap:.5rem">'
            .self::svg($key, ['width' => '18', 'height' => '18']).e($icon[0]).'</span>')->all();
    }
}
