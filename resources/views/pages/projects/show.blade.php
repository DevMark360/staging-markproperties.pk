@php
    $images = collect($project['gallery'])->map(fn ($file) => \App\Support\Projects::imageUrl($file, 'projects/gallery'));
    // Small copies for the viewer's thumbnail strip (P4); the full photo when there isn't one
    $thumbs = collect($project['gallery'])->map(fn ($file) => is_file(public_path('images/projects/gallery/thumbs/'.basename($file))) ? asset('images/projects/gallery/thumbs/'.basename($file)) : \App\Support\Projects::imageUrl($file, 'projects/gallery'));
    if ($images->isEmpty() && $project['image']) {
        $images = $thumbs = collect([\App\Support\Projects::imageUrl($project['image'])]);
    }
    $location = $project['area'].', '.$city;
    $mapQuery = $project['latitude'] ? $project['latitude'].','.$project['longitude'] : $project['name'].', '.$project['address'];
    $breadcrumbs = [
        ['Home', route('home')],
        ['Projects', route('projects.index')],
        [$city, route('projects.index', ['city' => $project['city']])],
        [$project['name'], null],
    ];

    // SEO: a specific description from the project's own copy (≈155 chars), with a generic fallback
    $intro = $project['highlights']['subtitle'] ?? null;
    $firstParagraph = preg_match('#<p>(.*?)</p>#s', $project['description'], $m) ? trim(html_entity_decode(strip_tags($m[1]))) : '';
    $metaDescription = \Illuminate\Support\Str::limit(
        trim(($intro ? $intro.'. ' : '').$firstParagraph) ?: $project['name'].($developer ? ' by '.$developer['name'] : '').' in '.$location.'. Explore the project, amenities and location with Mark Properties.',
        155,
    );

    // Structured data for search engines (no prices)
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => array_values(array_filter([
            array_filter([
                // What the project is (P3): homes are a Residence / ApartmentComplex; plots, shops, offices and mixed-use a Place
                '@type' => match ($typeName) { 'Apartments' => 'ApartmentComplex', 'Houses & Villas' => 'Residence', default => 'Place' },
                'name' => $project['name'],
                'url' => route('projects.show', $project['slug']),
                'description' => $metaDescription,
                'image' => $images->take(6)->values()->all(),
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $project['address'] ?: null,
                    'addressLocality' => $city,
                    'addressRegion' => $project['area'],
                    'addressCountry' => 'PK',
                ]),
                'geo' => $project['latitude'] ? ['@type' => 'GeoCoordinates', 'latitude' => $project['latitude'], 'longitude' => $project['longitude']] : null,
                'amenityFeature' => array_map(fn ($name) => ['@type' => 'LocationFeatureSpecification', 'name' => $name, 'value' => true], [...$project['amenities'], ...$project['utilities']]) ?: null,
                'containedInPlace' => ['@type' => 'Place', 'name' => $location],
            ]),
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => array_map(fn ($crumb, $i) => array_filter([
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $crumb[0],
                    'item' => $crumb[1] ?? route('projects.show', $project['slug']),
                ]), $breadcrumbs, array_keys($breadcrumbs)),
            ],
        ])),
    ];
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app :title="$project['name'].' in '.$location" enquire-on-page :share="$project['name']"
    :description="$metaDescription" :image="$images->first()">

    {{-- ───────────── Title ───────────── --}}
    <section class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8 lg:pt-8">
        <nav aria-label="Breadcrumb" class="scrollbar-none overflow-x-auto">
            <ol class="flex items-center gap-2 whitespace-nowrap text-xs text-muted">
                @foreach ($breadcrumbs as [$label, $url])
                    <li class="flex items-center gap-2">
                        @if ($url)
                            <a href="{{ $url }}" class="transition-colors hover:text-brand">{{ $label }}</a>
                            <svg class="h-3 w-3 text-line" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @else
                            <span class="font-semibold text-ink" aria-current="page">{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="mt-6 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="animate-rise">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($status)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand px-3 py-1 text-xs font-semibold text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-white"></span>{{ $status }}
                        </span>
                    @endif
                    @if ($typeName)
                        <span class="rounded-full bg-mist px-3 py-1 text-xs font-semibold text-ink-soft">{{ $typeName }}</span>
                    @endif
                </div>
                <h1 class="text-page-title mt-4 text-balance leading-[1.1] text-ink font-medium">{{ $project['name'] }}</h1>
                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm">
                    @if ($developer)
                        <a href="{{ route('developers.show', $developer['slug']) }}" class="group inline-flex items-center gap-2.5 font-semibold text-ink">
                            @if ($developer['logo'])
                                {{-- Same optically sized grey logo as the rest of the site (colour on hover) --}}
                                <span class="grid h-12 min-w-12 place-items-center rounded-lg bg-white px-2.5 ring-1 ring-line">
                                    <x-builder-logo :developer="$developer" size="2.3rem" />
                                </span>
                            @endif
                            <span>by <span class="transition-colors group-hover:text-brand">{{ $developer['name'] }}</span></span>
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-2 text-ink-soft">
                        <svg class="h-4 w-4 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        {{ $location }}
                    </span>
                </div>
            </div>

            {{-- Share lives in the header as an icon (layout :share) --}}
        </div>
    </section>

    {{-- ───────────── Gallery ───────────── --}}
    @if ($images->isNotEmpty())
        @php
            // Gallery layout. Phones (like the reference): one large photo, then up to 4 in two columns; an odd last one
            // spans the row so there's never a gap. From 640px: large photo on the left, the rest beside it.
            $count = $images->count();
            $shown = $images->take(5);
            $small = $shown->count() - 1;
            $cell = function (int $k) use ($count, $small): string {
                if ($k === 0) {
                    return 'col-span-2 aspect-[4/3] sm:aspect-auto '.match (true) {
                        $count >= 3 => 'sm:col-span-2 sm:row-span-2',
                        $count === 2 => 'sm:col-span-1',
                        default => 'sm:col-span-2',
                    };
                }
                $phone = $small % 2 === 1 && $k === $small ? 'col-span-2 aspect-[16/9]' : 'aspect-[4/3]';
                $wide = match (true) {
                    $count === 2 => 'sm:col-span-1',
                    $count === 3 => 'sm:col-span-2',          // two stacked on the right
                    $count === 4 && $k === 3 => 'sm:col-span-2', // fills the last right-hand cell
                    default => 'sm:col-span-1',
                };
                return $phone.' sm:aspect-auto '.$wide;
            };
        @endphp
        <section x-data="gallery({{ $count }})" class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div @class([
                'relative grid grid-cols-2 gap-2 overflow-hidden rounded-[18px] sm:gap-3',
                'sm:h-[clamp(18rem,52vw,34rem)]' => $count !== 2,
                'sm:grid-cols-4 sm:grid-rows-2' => $count >= 3,
                // Two photos side by side: a shorter row, close to a landscape photo's shape, so less empty edge
                'sm:grid-cols-2 sm:h-[clamp(14rem,27vw,23rem)]' => $count === 2,
            ])>
                @foreach ($shown as $k => $src)
                    <button type="button" @click="show({{ $k }})" aria-label="View photo {{ $k + 1 }} of {{ $count }}"
                        class="group relative overflow-hidden bg-mist {{ $cell($k) }}">
                        {{-- The whole photo is shown, never cropped; a blurred copy of it fills the space around it --}}
                        <img src="{{ $src }}" alt="" aria-hidden="true" @if ($k) loading="lazy" @endif
                            class="absolute inset-0 h-full w-full scale-110 object-cover opacity-70 blur-2xl">
                        <img src="{{ $src }}" alt="{{ $project['name'] }}, photo {{ $k + 1 }}" @if ($k) loading="lazy" @else fetchpriority="high" @endif
                            class="relative h-full w-full object-contain transition-transform duration-[900ms] ease-out-expo group-hover:scale-[1.03]">
                        <span class="absolute inset-0 bg-ink/0 transition-colors duration-500 group-hover:bg-ink/15"></span>
                    </button>
                @endforeach

                @if ($count > 1)
                    {{-- One "show all" button over the corner (phones and desktop alike) --}}
                    <button type="button" @click="show(0)"
                        class="absolute bottom-3 right-3 inline-flex min-h-11 items-center gap-2 rounded-xl bg-white px-4 text-sm font-semibold text-ink shadow-[0_10px_28px_-10px_rgba(20,20,20,.55)] transition-transform duration-300 hover:-translate-y-0.5 sm:bottom-4 sm:right-4">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="5" r="2"/><circle cx="12" cy="5" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="12" cy="19" r="2"/><circle cx="19" cy="19" r="2"/></svg>
                        Show all {{ $count }} photos
                    </button>
                @endif
            </div>

            {{-- Lightbox --}}
            <template x-teleport="body">
                <div x-cloak x-show="open" x-trap.inert="open" @keydown.window.escape="open = false" @keydown.window.arrow-right="open && next()" @keydown.window.arrow-left="open && prev()"
                    x-effect="document.documentElement.classList.toggle('overflow-hidden', open)"
                    x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[70] flex flex-col bg-ink/95 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="{{ $project['name'] }} photos">
                    <div class="flex items-center justify-between px-5 py-4 text-white">
                        <p class="text-sm font-semibold">{{ $project['name'] }} <span class="ms-2 font-medium tabular-nums text-white/60"><span x-text="i + 1"></span> / {{ $images->count() }}</span></p>
                        <button type="button" @click="open = false" aria-label="Close photos" class="grid h-10 w-10 place-items-center rounded-full border border-white/20 transition-all duration-300 hover:rotate-90 hover:border-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <div class="relative flex flex-1 items-center justify-center px-4" @touchstart.passive="swipeStart($event)" @touchend.passive="swipeEnd($event)">
                        @foreach ($images as $k => $src)
                            <img x-show="i === {{ $k }}" src="{{ $src }}" alt="{{ $project['name'] }}, photo {{ $k + 1 }}" loading="lazy"
                                x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                class="max-h-[76svh] max-w-full rounded-xl object-contain shadow-2xl">
                        @endforeach
                        @if ($images->count() > 1)
                            <button type="button" @click="prev()" aria-label="Previous photo" class="absolute left-4 top-1/2 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white backdrop-blur transition-colors hover:bg-white/20">
                                <svg class="h-5 w-5 rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                            <button type="button" @click="next()" aria-label="Next photo" class="absolute right-4 top-1/2 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white backdrop-blur transition-colors hover:bg-white/20">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        @endif
                    </div>
                    @if ($images->count() > 1)
                        <div class="scrollbar-none flex justify-center gap-2 overflow-x-auto px-5 py-4">
                            @foreach ($images as $k => $src)
                                <button type="button" @click="i = {{ $k }}" aria-label="Show photo {{ $k + 1 }}"
                                    class="h-14 w-20 shrink-0 overflow-hidden rounded-lg ring-2 transition-all duration-300"
                                    :class="i === {{ $k }} ? 'opacity-100 ring-brand' : 'opacity-50 ring-transparent hover:opacity-80'">
                                    <img src="{{ $thumbs[$k] }}" alt="" loading="lazy" width="240" height="135" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </template>
        </section>
    @endif

    {{-- ───────────── Content + sticky enquiry ───────────── --}}
    <div x-data class="mx-auto grid max-w-7xl gap-12 px-4 py-12 sm:px-6 lg:grid-cols-12 lg:gap-14 lg:px-8 lg:py-16">
        <div class="min-w-0 space-y-6 lg:col-span-8">

            {{-- About --}}
            @if ($project['description'])
                <section x-data="{ more: false, long: false }" x-init="long = $refs.body.scrollHeight > 520"
                    class="content-card">
                    <h2 class="text-section font-bold tracking-tight text-ink">About {{ $project['name'] }}</h2>
                    <div class="relative mt-6">
                        <div x-ref="body" class="space-y-4 overflow-hidden text-[15px] leading-relaxed text-ink-soft transition-[max-height] duration-700 ease-out-expo [&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-ink [&_h3]:mt-8 [&_h3]:text-lg [&_h3]:font-bold [&_h3]:text-ink [&_li]:ps-1 [&_li]:marker:text-brand [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:ps-5 [&_strong]:font-semibold [&_strong]:text-ink [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:ps-5 [&>*:first-child]:mt-0"
                            :style="long && ! more ? 'max-height: 520px' : 'max-height: ' + ($refs.body.scrollHeight + 40) + 'px'">
                            {!! $project['description'] !!}
                        </div>
                        <div x-show="long && ! more" class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-linear-to-t from-white to-transparent"></div>
                    </div>
                    <button x-cloak x-show="long" type="button" @click="more = ! more"
                        class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-ink transition-colors hover:text-brand">
                        <span x-text="more ? 'Show less' : 'Read more'">Read more</span>
                        <svg class="h-4 w-4 transition-transform duration-300" :class="more && 'rotate-180'" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </section>
            @endif

            {{-- Highlights: one card, a plain tick list (no box per point) --}}
            @if ($highlights = $project['highlights'])
                <section aria-label="Highlights" class="content-card">
                    @if ($highlights['title'])
                        <h2 class="text-card-title font-bold leading-snug tracking-tight text-ink">{{ $highlights['title'] }}</h2>
                    @endif
                    @if ($highlights['subtitle'])
                        <p class="mt-2 text-[15px] text-muted">{{ $highlights['subtitle'] }}</p>
                    @endif
                    @if ($highlights['points'])
                        <ul class="mt-5 divide-y divide-line">
                            @foreach ($highlights['points'] as $point)
                                <li class="flex items-start gap-3 py-3.5 first:pt-0 last:pb-0">
                                    <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-50 text-brand">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m5 10.5 3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="text-[15px] leading-relaxed text-ink-soft">{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif

            {{-- Amenities --}}
            @if ($project['amenities'] || $project['utilities'])
                <section class="content-card">
                    <h2 class="text-section font-bold tracking-tight text-ink">Amenities &amp; features</h2>
                    @foreach (['Amenities' => $project['amenities'], 'Utilities' => $project['utilities']] as $group => $items)
                        @if ($items)
                            <h3 class="mt-6 text-sm font-bold text-ink">{{ $group }}</h3>
                            <ul class="mt-3 grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4">
                                @foreach ($items as $item)
                                    <li class="flex items-center gap-3 rounded-xl bg-mist p-3">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white text-brand">
                                            <x-amenity-icon :name="$item" class="h-5 w-5" />
                                        </span>
                                        <span class="text-sm font-semibold leading-snug text-ink">{{ \Illuminate\Support\Str::title(strtolower($item)) === $item ? $item : ucfirst($item) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach
                </section>
            @endif

            {{-- Location --}}
            <section class="content-card">
                <h2 class="text-section font-bold tracking-tight text-ink">Location</h2>
                <p class="mt-3 flex items-start gap-2 text-[15px] text-ink-soft">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    {{ $project['address'] ?: $location }}
                </p>
                <x-map-embed :src="'https://www.google.com/maps?q='.rawurlencode($mapQuery).'&z=15&output=embed'" :title="'Map of '.$project['name']"
                    :address="$project['address'] ?: $location" class="mt-5 h-80 rounded-xl sm:h-96">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($mapQuery) }}" target="_blank" rel="noopener"
                        class="btn btn-primary btn-sm absolute bottom-4 right-4 shadow-lg">Get directions</a>
                </x-map-embed>
            </section>
        </div>

        {{-- Enquiry (sticky on desktop) --}}
        <aside class="lg:col-span-4">
            <div id="enquire" class="lg:sticky lg:top-24" x-data="enquiryForm('general')" @enquire.window="open($event.detail)">
                <div class="overflow-hidden rounded-[18px] border border-line bg-white shadow-[0_30px_60px_-36px_rgba(20,20,20,.45)]">
                    <div class="border-b border-line bg-mist px-6 py-5">
                        <p class="text-lg font-bold text-ink">Interested in {{ $project['name'] }}?</p>
                        <p class="mt-1 text-sm text-muted">An advisor will call you back with the details.</p>
                    </div>

                    {{-- Success --}}
                    <div x-cloak x-show="done || {{ session('enquiry_sent') ? 'true' : 'false' }}"
                        x-transition:enter="transition duration-700 ease-out-expo" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="px-6 py-10 text-center" role="status">
                        <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand text-white shadow-[0_12px_28px_-10px_rgba(220,26,33,.9)]">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <p class="mt-5 text-lg font-bold text-ink">Thank you!</p>
                        <p class="mt-1 text-sm text-muted">An advisor will contact you shortly about {{ $project['name'] }}.</p>
                        @php($brochureUrl = session('brochure_url'))
                        <a x-show="brochure || {{ $brochureUrl ? 'true' : 'false' }}" :href="brochure ?? @js($brochureUrl)" href="{{ $brochureUrl }}" target="_blank" rel="noopener" download
                            class="btn btn-dark mt-6">
                            Download brochure
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 4v12m0 0-4-4m4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>

                    {{-- Form --}}
                    <form x-show="! done && ! {{ session('enquiry_sent') ? 'true' : 'false' }}" method="POST" action="{{ route('enquiries.store') }}"
                        @submit.prevent="submit($el)" novalidate class="space-y-4 p-6">
                        @csrf
                        <input type="hidden" name="project" value="{{ $project['slug'] }}">
                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <fieldset>
                            <legend class="text-sm font-bold text-ink">I'd like</legend>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                {{-- Short labels on the buttons; the admin and CSV keep the full names --}}
                                @foreach ($interests as $value => $label)
                                    <label class="cursor-pointer" title="{{ $label }}">
                                        <input type="radio" name="interest" value="{{ $value }}" x-model="interest" class="peer sr-only">
                                        <span class="flex h-full items-center justify-center min-h-11 rounded-xl border border-line px-1.5 py-2 text-center text-[13px] font-semibold leading-tight text-ink-soft transition-all duration-300 hover:border-ink/40 peer-checked:border-brand peer-checked:bg-brand-50 peer-checked:text-brand peer-focus-visible:ring-4 peer-focus-visible:ring-brand/30">{{ ['general' => 'Enquiry', 'site-visit' => 'Site visit', 'brochure' => 'Brochure'][$value] ?? $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- Visible labels above each field (P5), as on the Contact and Partner forms; the placeholder is only an example --}}
                        @foreach ([
                            ['name', 'text', 'Full name', 'Your name', 'name', true],
                            ['phone', 'tel', 'Phone number', '0300 1234567', 'tel', true],
                            ['email', 'email', 'Email', 'you@example.com', 'email', false],
                        ] as [$field, $type, $label, $placeholder, $autocomplete, $required])
                            <label class="block">
                                <span class="mb-1.5 block text-[13px] font-semibold text-ink">{{ $label }} @unless ($required)<span class="font-normal text-muted">(optional)</span>@endunless</span>
                                <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" placeholder="{{ $placeholder }}" autocomplete="{{ $autocomplete }}" @if ($required) required @endif @if ($type === 'tel') inputmode="tel" x-phone @endif
                                    class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm text-ink transition-all duration-200 placeholder:text-muted/70 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5">
                            </label>
                        @endforeach
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-semibold text-ink">Message <span class="font-normal text-muted">(optional)</span></span>
                            <textarea name="message" rows="3" placeholder="Anything you’d like to know"
                                class="w-full resize-none rounded-xl border border-line bg-white px-4 py-3 text-sm text-ink transition-all duration-200 placeholder:text-muted/70 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5">{{ old('message') }}</textarea>
                        </label>

                        <button type="submit" :disabled="sending"
                            class="btn btn-primary w-full disabled:cursor-wait disabled:opacity-75">
                            <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            <span x-text="sending ? 'Sending…' : (interest === 'brochure' ? 'Get the brochure' : 'Send request')">Send request</span>
                        </button>

                        <p x-show="error" x-text="error" x-cloak class="text-sm font-medium text-brand" role="alert"></p>
                        @if ($errors->any())
                            <p x-show="! error" class="text-sm font-medium text-brand" role="alert">{{ $errors->first() }}</p>
                        @endif
                        <p class="text-xs leading-relaxed text-muted">By submitting you agree to be contacted by Mark Properties about this project. See our <a href="{{ route('privacy') }}" class="underline underline-offset-2 hover:text-ink">privacy policy</a>.</p>
                    </form>

                </div>
            </div>
        </aside>
    </div>

    {{-- ───────────── Similar projects ───────────── --}}
    @if ($similar->isNotEmpty())
        <section x-data="carousel" class="overflow-hidden border-t border-line bg-mist py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-section-header title="Similar projects" action="All projects" :href="route('projects.index')" />
                <div x-ref="track" @scroll.passive="update()" @resize.window.debounce="update()"
                    class="scrollbar-none -mx-4 mt-10 flex items-start snap-x sm:items-stretch snap-mandatory gap-5 overflow-x-auto scroll-px-4 px-4 pb-6 pt-1 sm:-mx-6 sm:scroll-px-6 sm:px-6 lg:mx-0 lg:scroll-px-0 lg:px-0">
                    @foreach ($similar as $other)
                        <div class="flex w-[82%] shrink-0 snap-start sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] xl:w-[calc((100%-3.75rem)/4)]">
                            <x-project-card :project="$other" :developer="$developers->get($other['developer'])" class="w-full" />
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-1 flex-1 overflow-hidden rounded-full bg-ink/10" aria-hidden="true">
                        <div class="h-full rounded-full bg-brand transition-[width] duration-300 ease-out" :style="{ width: Math.max(progress, 0.08) * 100 + '%' }"></div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="slide(-1)" :disabled="atStart" aria-label="Previous projects" class="grid h-11 w-11 place-items-center rounded-full border border-ink/15 bg-white text-ink transition-all duration-300 hover:border-ink active:scale-90 disabled:opacity-35">
                            <svg class="h-4 w-4 rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" @click="slide(1)" :disabled="atEnd" aria-label="Next projects" class="grid h-11 w-11 place-items-center rounded-full border border-ink/15 bg-white text-ink transition-all duration-300 hover:border-ink active:scale-90 disabled:opacity-35">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ───────────── Mobile action bar ───────────── --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-white/95 p-3 backdrop-blur-md lg:hidden">
        <div class="flex gap-2">
            <button type="button" @click="$dispatch('enquire', { interest: 'general' })" x-data
                class="btn btn-primary flex-1">Enquire now</button>
        </div>
    </div>
    <div class="h-20 lg:hidden" aria-hidden="true"></div>
</x-layouts.app>
