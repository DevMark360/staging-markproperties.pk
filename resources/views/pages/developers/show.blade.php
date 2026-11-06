@php
    $list = fn (array $items) => count($items) > 1 ? implode(', ', array_slice($items, 0, -1)).' and '.end($items) : ($items[0] ?? '');
    $areaList = $list($areas->take(5)->all());
    $intro = $developer['name'].($developer['partner'] ? ' is a partner developer of Mark Properties' : ' is a developer marketed by Mark Properties')
        .' with off-plan projects in '.$areaList.($cityNames->count() === 1 ? ', '.$cityNames->first() : ' across '.$list($cityNames->all())).'.';
    $coverImage = $cover ? ($cover['hero'] ? \App\Support\Projects::imageUrl($cover['hero'], 'hero') : \App\Support\Projects::imageUrl($cover['image'])) : null;
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $developer['name'],
        'url' => route('developers.show', $developer['slug']),
        'logo' => \App\Support\Projects::fileUrl($developer['logo'], 'images/developers'),
        'description' => $intro,
        'areaServed' => $cityNames->map(fn ($c) => ['@type' => 'City', 'name' => $c])->all(),
    ]);
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app :title="$developer['name'].' Projects'" :description="$developer['name'].' off-plan projects in '.$list($cityNames->all()).'. See their new launches and ask a Mark Properties advisor for details.'" :header-enquire="false" :image="$coverImage">

    {{-- ───────────── Builder card (reference layout, no background image) ───────────── --}}
    <section class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 sm:pt-10 lg:px-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-sm text-muted">
                <li><a href="{{ route('home') }}" class="transition-colors hover:text-ink">Home</a></li>
                <li aria-hidden="true"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></li>
                <li><a href="{{ route('developers.index') }}" class="transition-colors hover:text-ink">Developers</a></li>
                <li aria-hidden="true"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></li>
                <li class="text-ink" aria-current="page">{{ $developer['name'] }}</li>
            </ol>
        </nav>

        <div class="content-card mt-5">
            <div class="flex items-center gap-3 sm:gap-6">
                <span class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl border border-line bg-white p-1.5 sm:h-24 sm:w-24 sm:p-2">
                    @if ($developer['logo'])
                        <img src="{{ \App\Support\Projects::fileUrl($developer['logo'], 'images/developers') }}" alt="{{ $developer['name'] }} logo" width="320" height="320" class="h-full w-full object-contain">
                    @else
                        <span class="text-3xl font-bold text-ink">{{ str($developer['name'])->substr(0, 1) }}</span>
                    @endif
                </span>
                <div class="min-w-0 border-s border-line ps-3 sm:ps-6">
                    @if ($developer['partner'])
                        <span class="mb-2 inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m5 10.5 3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Mark Properties partner
                        </span>
                    @endif
                    <h1 class="text-page-title font-medium leading-[1.1] text-ink">{{ $developer['name'] }}</h1>
                    <p class="mt-2 flex items-start gap-1.5 text-sm text-ink-soft"><svg class="h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg><span>{{ $cityNames->implode(', ') }}</span></p>
                </div>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                <a href="{{ route('developers.show', ['slug' => $developer['slug'], 'view' => 'map']) }}#projects" x-data @click.prevent="$dispatch('developer-map')" class="btn btn-primary gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    View on map
                </a>
                <a href="{{ route('contact') }}" class="btn btn-outline gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 20 5.4 15.8A8 8 0 1 1 8.4 18.7L4 20Z" stroke-linejoin="round"/></svg>
                    Enquire now
                </a>
            </div>
        </div>
    </section>

    {{-- ───────────── Projects: filters + grid / map, all on this page ───────────── --}}
    @php
        // Place filter: cities when the builder works in more than one, otherwise the areas of that one city
        $byCity = $cityNames->count() > 1;
        $items = $projects->map(fn ($p) => ['slug' => $p['slug'], 'type' => $p['type'], 'place' => $byCity ? $p['city'] : $p['area'], 'area' => $p['area'], 'status' => $p['status']])->values();
        // A builder in several cities also gets an Area filter, grouped by city and narrowed to the chosen city
        $areaGroups = $byCity
            ? $projects->groupBy('city')->sortBy(fn ($list, $city) => \App\Support\Projects::cityName($city))->map(fn ($list) => $list->pluck('area', 'area')->sort())
            : collect();
        $placeOptions = $byCity
            ? $projects->pluck('city')->unique()->mapWithKeys(fn ($c) => [$c => \App\Support\Projects::cityName($c)])->sort()
            : $areas->mapWithKeys(fn ($a) => [$a => $a]);
        $typeOptions = $projects->pluck('type')->unique()->filter()->mapWithKeys(fn ($t) => [$t => \App\Support\Projects::typeName($t)])->sort();
        $stageOptions = $stages;
        // Only filters with a real choice
        $selects = array_filter([
            ['type', 'Property type', 'All types', $typeOptions],
            ['place', $byCity ? 'City' : 'Area', $byCity ? 'All cities' : 'All areas', $placeOptions],
            ['area', 'Area', 'All areas', $areaGroups->flatten()->unique()],
            ['status', 'Stage', 'All stages', $stageOptions],
        ], fn ($f) => $f[3]->count() > 1);
        $selectClass = 'w-full cursor-pointer appearance-none rounded-xl border border-line bg-white py-2.5 pe-9 ps-3.5 text-sm font-semibold text-ink transition-colors duration-200 hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5';
        $chevron = '<svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    @endphp
    <section id="projects" class="mx-auto max-w-7xl scroll-mt-24 px-4 pb-16 pt-10 sm:px-6 sm:pt-14 lg:px-8"
        x-data="developerProjects(@js($view), @js($points), @js($items))" @developer-map.window="openMap()">
        <h2 class="heading-mark text-section font-bold tracking-tight text-ink">Projects by {{ $developer['name'] }}</h2>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            @foreach ($selects as [$key, $label, $all, $options])
                <label class="block min-w-0 flex-1 basis-[calc(50%-0.75rem)] sm:max-w-56 sm:basis-44">
                    <span class="sr-only">{{ $label }}</span>
                    <span class="relative block">
                        @if ($key === 'area')
                            {{-- Areas by city; once a city is picked, only its areas --}}
                            <select x-combo x-model="area" x-data="{ groups: @js($areaGroups), cityNames: @js($areaGroups->keys()->mapWithKeys(fn ($c) => [$c => \App\Support\Projects::cityName($c)])) }" class="{{ $selectClass }}">
                                <option value="">{{ $all }}</option>
                                <template x-for="[group, list] in Object.entries(place ? { [place]: groups[place] ?? {} } : groups)" :key="group">
                                    <optgroup :label="cityNames[group]">
                                        <template x-for="name in Object.values(list)" :key="name">
                                            <option :value="name" x-text="name" :selected="name === area"></option>
                                        </template>
                                    </optgroup>
                                </template>
                            </select>
                        @else
                            <select x-combo x-model="{{ $key }}" class="{{ $selectClass }}">
                                <option value="">{{ $all }}</option>
                                @foreach ($options as $value => $name)
                                    <option value="{{ $value }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        @endif
                        {!! $chevron !!}
                    </span>
                </label>
            @endforeach
            <button type="button" x-show="filtered" x-cloak @click="clear()" class="hit-area py-2.5 text-sm font-semibold text-brand">Clear all</button>

            {{-- Grid / map (same switch as the projects page) --}}
            <div class="relative ms-auto inline-flex shrink-0 rounded-full border border-line bg-white p-1" role="group" aria-label="View">
                <span class="pointer-events-none absolute inset-y-1 w-[calc(50%-4px)] rounded-full bg-brand shadow-[0_8px_18px_-8px_rgba(220,26,33,.9)] transition-transform duration-500 ease-out-expo"
                    :class="view === 'map' ? 'translate-x-full' : 'translate-x-0'"></span>
                @foreach (['grid' => 'Grid', 'map' => 'Map'] as $mode => $label)
                    <button type="button" @click="view = '{{ $mode }}'" :aria-pressed="view === '{{ $mode }}'"
                        class="hit-area relative z-10 inline-flex w-[4.5rem] items-center justify-center gap-1.5 rounded-full py-1.5 text-sm font-semibold transition-colors duration-300 sm:w-24 sm:gap-2"
                        :class="view === '{{ $mode }}' ? 'text-white' : 'text-ink-soft hover:text-ink'">
                        @if ($mode === 'grid')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                        @else
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m9 4-6 2.5v13.5l6-2.5 6 2.5 6-2.5V4l-6 2.5L9 4ZM9 4v13.5M15 6.5V20" stroke-linejoin="round"/></svg>
                        @endif
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Grid --}}
        <div x-show="view === 'grid'" @if ($view === 'map') x-cloak @endif>
            <ul class="mt-6 grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 sm:gap-y-8 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($projects as $project)
                    <li class="flex" x-show="shown(@js($project['slug']))" @if ($loop->index >= 12) x-cloak @endif
                        x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                        <x-project-card :project="$project" :developer="$developer" :show-developer="false" compact class="w-full" />
                    </li>
                @endforeach
            </ul>
            <div x-show="! matches.length" x-cloak class="mt-6 rounded-[18px] border border-dashed border-line px-6 py-10 text-center">
                <p class="font-semibold text-ink">No {{ $developer['name'] }} projects match these filters.</p>
                <button type="button" @click="clear()" class="btn btn-outline mt-4">Clear filters</button>
            </div>
            @if (count($projects) > 12)
                <div class="mt-10 text-center" x-show="limit < matches.length" x-cloak>
                    <button type="button" @click="limit += 12" class="btn btn-outline">Show more projects</button>
                </div>
            @endif
        </div>

        {{-- Map: only this builder's projects, following the filters --}}
        <div x-show="view === 'map'" @if ($view !== 'map') x-cloak @endif
            x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="mt-6">
            <div class="relative isolate overflow-hidden rounded-[18px] border border-line shadow-[0_30px_60px_-40px_rgba(20,20,20,.45)]">
                {{-- svh: a fixed height that does not change when the phone browser bar shows or hides while scrolling --}}
                <div x-ref="map" data-style="{{ config('site.map_style') }}" class="h-[75svh] min-h-[26rem] max-h-[44rem] w-full bg-mist" role="region" aria-label="Map of {{ $developer['name'] }} projects"></div>
                {{-- The map couldn't load (no WebGL, offline, or the map service is down): say so and offer the grid (C11) --}}
                <div x-show="mapFailed" x-cloak role="alert" class="absolute inset-0 z-[600] grid place-items-center bg-mist p-6 text-center">
                    <div class="max-w-sm">
                        <p class="text-lg font-bold text-ink">The map couldn’t load</p>
                        <p class="mt-2 text-sm text-muted">It may be your connection, or this device can’t show maps. You can try again or see the projects as a list.</p>
                        <div class="mt-5 flex flex-wrap justify-center gap-2">
                            <button type="button" @click="retryMap()" class="btn btn-dark btn-sm">Try again</button>
                            <button type="button" @click="view = 'grid'" class="btn btn-outline btn-sm">Show the grid</button>
                        </div>
                    </div>
                </div>
                <p x-show="! matches.length" x-cloak class="absolute left-4 top-4 z-[500] rounded-full bg-white/95 px-4 py-2 text-xs font-semibold text-ink shadow-lg">No projects match these filters</p>
            </div>
        </div>
    </section>

    <x-cta-band :title="'Interested in a '.$developer['name'].' project?'"
        lead="Talk to a Mark Properties advisor for project details, site visits and booking support." />
</x-layouts.app>
