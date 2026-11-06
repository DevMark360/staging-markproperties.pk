<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Writes short overview sections and highlights for projects whose imported copy is thin,
 * using only facts we hold (developer, stage, location, address, amenities, existing bullets).
 * Projects with substantial imported copy are left untouched.
 */
class ProjectCopy
{
    /** Below this many characters of plain text, a description counts as thin. */
    private const THIN = 300;

    /**
     * @param  array<string, mixed>  $project  from Projects::find()
     * @return array<string, mixed>
     */
    public static function fill(array $project): array
    {
        $developer = Projects::developers()->firstWhere('slug', $project['developer']);
        $city = Projects::cityName($project['city']);
        $highlights = $project['highlights'];
        $points = array_map(static::tidy(...), $highlights['points'] ?? []);
        if ($highlights) {
            $highlights['title'] = $highlights['title'] ? static::tidy($highlights['title']) : null;
            $highlights['subtitle'] = $highlights['subtitle'] ? static::tidy($highlights['subtitle']) : null;
        }
        $intro = static::clean(html_entity_decode(strip_tags($project['description']), ENT_QUOTES | ENT_HTML5));
        $kind = static::kindOf($project);

        if (mb_strlen($intro) < self::THIN) {
            $project['description'] = static::description($project, $developer, $city, $kind, $intro);
        }

        if (count($points) < 3) {
            $points = static::points($project, $developer, $city, $kind, $points);
            $highlights = [
                'title' => $highlights['title'] ?? 'Why choose '.$project['name'],
                'subtitle' => $highlights['subtitle'] ?? Str::ucfirst($kind).($developer ? ' by '.$developer['name'] : '').' in '.$project['area'].', '.$city,
                'points' => $points,
            ];
        } else {
            $highlights['points'] = $points;
        }
        $project['highlights'] = $highlights;

        return $project;
    }

    private static function description(array $project, ?array $developer, string $city, string $kind, string $intro): string
    {
        $name = e($project['name']);
        $area = e($project['area']);
        $by = $developer ? ' by <strong>'.e($developer['name']).'</strong>' : '';
        // Wording for the stages whose meaning is known; any other stage the admin adds reads as "a new"
        $status = (string) $project['status'];
        $stage = match ($status) {
            'pre-launch' => 'a newly launched',
            'under-construction' => 'an under-construction',
            'ready' => 'a ready-for-possession',
            default => 'a new',
        };

        $sections = [];

        // 1. The project
        $place = $project['area'] === $city ? e($city) : "{$area}, ".e($city);
        $about = "<strong>{$name}</strong> is {$stage} {$kind} project{$by} in {$place}.";
        if ($intro !== '' && ! preg_match('/\b(situated|located)\b/i', $intro)) {
            $about .= ' '.e(Str::finish($intro, '.'));
        }
        if ($status === 'pre-launch') {
            $about .= ' Pre-launch bookings give buyers an early entry into the project.';
        } elseif ($status === 'ready') {
            $about .= ' It suits buyers looking to move in or rent out sooner.';
        }
        $heading = match ($kind) {
            'commercial' => 'Commercial Spaces',
            'mixed-use' => 'Mixed-Use Development',
            'residential' => 'Residential Project',
            default => Str::title($kind),
        };
        $sections[] = [$heading.($developer ? ' by '.$developer['name'] : ' in '.$project['area']), $about];

        // 2. Location — addresses are sometimes phrases ("Near …", "Conveniently located at …")
        $address = rtrim(static::clean((string) $project['address']), '.,; ');
        // "Located At The Intersection Of …" → "located at the intersection of …"
        $address = preg_replace_callback('/(?<=\s)(At|The|Of|And|Near|To|In|On|From|With|For|By)\b/', fn ($m) => Str::lower($m[1]), $address);
        if ($address !== '' && ! Str::contains(Str::lower($address), Str::lower($city))) {
            $address .= ', '.$city;
        }
        $location = match (true) {
            $address === '' => "The project is located in {$place}.",
            (bool) preg_match('/^(?:conveniently\s+|strategically\s+)?(?:located|situated)\s+/i', $address) => 'The project is '.e(preg_replace_callback('/^(?:\w+\s+)?(?:located|situated)/i', fn ($m) => Str::lower($m[0]), $address)).'.',
            (bool) preg_match('/^(?:near|opposite|opp\.?|along|on|off)\b/i', $address) => 'The project is located '.e(Str::lcfirst($address)).'.',
            default => 'The project is located at '.e($address).'.',
        };
        $location .= ' Our advisors can arrange a guided site visit so you can see the location in person.';
        $sections[] = ['Location in '.($project['area'] === $city ? $city : $project['area']), $location];

        // 3. Amenities & builder
        $amenities = array_slice(array_map(fn ($a) => Str::lower($a) === $a || Str::upper($a) === $a ? Str::title(Str::lower($a)) : $a, $project['amenities']), 0, 5);
        $utilities = array_slice($project['utilities'], 0, 4);
        $living = $amenities
            ? 'Residents can look forward to '.static::list(array_map('e', $amenities)).($utilities ? ', supported by '.static::list(array_map(fn ($u) => e(Str::lower($u)), $utilities)) : '').'.'
            : 'The project is planned around modern, family-friendly living with essential utilities and secure access.';
        if ($developer) {
            $count = Projects::all()->where('developer', $developer['slug'])->count();
            $living .= ' '.e($developer['name']).($count > 1 ? " currently has {$count} projects marketed by Mark Properties." : ' brings its experience to this development.');
        }
        $sections[] = ['Amenities & Everyday Living', $living];

        return collect($sections)->map(fn ($s) => '<h3>'.e($s[0]).'</h3><p>'.$s[1].'</p>')->implode("\n");
    }

    /** @param  string[]  $existing */
    private static function points(array $project, ?array $developer, string $city, string $kind, array $existing): array
    {
        $stageName = Projects::stageName($project['status']);
        $generated = array_filter([
            // Only real partners are called partners (I1); every other builder is just named
            $developer ? 'Developed by '.$developer['name'].($developer['partner'] ? ', a builder partnered with Mark Properties.' : '.') : null,
            'Located in '.($project['area'] === $city ? $city : $project['area'].', '.$city).'.',
            $stageName ? Str::ucfirst($stageName).' '.$kind.($project['status'] === 'pre-launch' ? ', open for early bookings.' : '.') : null,
            $project['amenities'] ? 'Amenities include '.static::list(array_map(fn ($a) => Str::lower($a), array_slice($project['amenities'], 0, 4))).'.' : null,
            $project['utilities'] ? 'Reliable '.static::list(array_map(fn ($u) => Str::lower($u), array_slice($project['utilities'], 0, 3))).' infrastructure.' : null,
        ]);

        return array_slice(array_values(array_unique([...$existing, ...$generated])), 0, 5);
    }

    /**
     * Listing filter type (a property type slug) for a project with its page details.
     *
     * @param  array<string, mixed>  $project  from Projects::find()
     */
    public static function type(array $project): string
    {
        return match (static::kindOf($project)) {
            'villas & bungalows', 'residential homes' => 'houses',
            'residential plots', 'residential & commercial plots' => 'plots',
            'commercial' => 'commercial',
            'mixed-use' => 'mixed-use',
            default => 'apartments',
        };
    }

    /** Signals per kind of project; matched against lower-cased text */
    private const SIGNALS = [
        'apartments' => '/\bapartments?\b|\bflats?\b|\btowers?\b|\bheights\b|\bresiden(cy|ce|ces|cia)\b|\b\d\s*(?:,\s*\d\s*)*(?:&|and)?\s*\d?\s*(?:bed|room)s?\b|\bpenthouses?\b|\bsuites?\b/',
        'houses' => '/\bvillas?\b|\bbungalows?\b|\bbunglows?\b|\bcottages?\b|\bhouses\b|\bhomes\b|\branch(es)?\b|\btownhouses?\b/',
        // "Plot No. 121" / "plot size" are addresses or site facts, not plot projects
        'plots' => '/\bplots\b(?!\s+(?:no|number|#))|\bplot\b(?!\s*(?:no\b|number|#|size|area|\d))/',
        'commercial' => '/\bmall\b|\bshops?\b|\bshowrooms?\b|\boffices?\b|\bplaza\b|\bbazaa?r\b|\bcommercial\b|\bbusiness\b|\bcorporate\b|\bretail\b|\boutlets?\b/',
    ];

    /** Kind of project, judged from its name (strongest), highlights title, then the rest of its copy */
    private static function kindOf(array $project): string
    {
        $highlights = $project['highlights'] ?? null;
        $sources = [
            [$project['name'], 6],
            [($highlights['title'] ?? '').' '.($highlights['subtitle'] ?? ''), 3],
            [implode(' ', $highlights['points'] ?? []).' '.html_entity_decode(strip_tags($project['description'] ?? ''), ENT_QUOTES | ENT_HTML5), 1],
        ];

        $score = array_fill_keys(array_keys(self::SIGNALS), 0);
        foreach ($sources as [$text, $weight]) {
            foreach (self::SIGNALS as $kind => $pattern) {
                $score[$kind] += preg_match_all($pattern, Str::lower($text)) * $weight;
            }
        }

        $commercial = $score['commercial'];
        unset($score['commercial']);
        arsort($score);
        $top = array_key_first($score);
        $residential = $score[$top];

        $label = ['apartments' => 'residential apartments', 'houses' => 'villas & bungalows', 'plots' => 'residential plots'][$top];

        return match (true) {
            $residential === 0 && $commercial === 0 => 'residential',
            $commercial > $residential && $residential < 3 => 'commercial',
            $top === 'plots' && $commercial >= 3 => 'residential & commercial plots',
            $commercial >= 6 && $commercial >= $residential * 0.6 => 'mixed-use',
            default => $label,
        };
    }

    /** Collapse whitespace, drop spaces before punctuation and repeated commas */
    private static function clean(string $text): string
    {
        $text = preg_replace(['/\s+/u', '/\s+([.,;:!?])/u', '/,(\s*,)+/u'], [' ', '$1', ','], $text);

        return trim($text);
    }

    /** ALL-CAPS bullets → sentence case; tidy spacing and the full stop */
    private static function tidy(string $point): string
    {
        $point = trim(preg_replace('/\s+/', ' ', $point));
        if ($point !== '' && Str::upper($point) === $point) {
            $point = Str::ucfirst(Str::lower($point));
            $point = preg_replace('/\bsq\.?\s?yds?\b/i', 'sq. yd', $point);
        }

        return $point;
    }

    /** a, b and c */
    private static function list(array $items): string
    {
        return count($items) > 1 ? implode(', ', array_slice($items, 0, -1)).' and '.end($items) : (string) ($items[0] ?? '');
    }
}
