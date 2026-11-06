@php
    $active = array_filter([
        'q' => $filters['q'] !== '' ? '“'.$filters['q'].'”' : null,
        'city' => $cityName,
        'type' => $filters['type'] ? $types[$filters['type']] : null,
        'status' => \App\Support\Projects::stageName($filters['status']),
        'developer' => $filters['developer'] ? $developers[$filters['developer']]['name'] : null,
        'area' => $filters['area'] ? ($areaName ?? \Illuminate\Support\Str::title(str_replace('-', ' ', $filters['area']))) : null,
    ]);
    $without = fn (string ...$keys) => request()->fullUrlWithoutQuery([...$keys, 'page']);
    // Stage links (desktop tabs and the phone filter panel) and whether anything is filtered (red dot on "Filters")
    $stageLinks = collect(['' => 'All stages', ...$statuses])
        ->map(fn ($label, $value) => ['label' => $label, 'href' => $value === '' ? $without('status') : request()->fullUrlWithQuery(['status' => $value, 'page' => null]), 'current' => $filters['status'] === ($value ?: null)]);
    $hasFilters = $active || $filters['sort'] !== 'newest';
    $selectClass = 'w-full cursor-pointer appearance-none rounded-2xl border border-line bg-white py-3.5 pe-12 ps-4 text-base font-medium md:rounded-xl md:py-2.5 md:pe-7 md:ps-3 md:text-sm md:font-semibold lg:pe-9 lg:ps-3.5 text-ink transition-colors duration-200 hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5';
    $chevron = '<svg class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-ink md:right-2.5 md:h-4 md:w-4 md:text-muted lg:right-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

<x-layouts.app :title="$heading" :canonical="$canonical"
    description="Browse new off-plan projects{{ $cityName ? ' in '.$cityName : ' across Pakistan' }} from trusted builders. Filter by city, property type and stage, or explore them on the map.">

    <div x-data="projectListing(@js($view), @js($points))">

        <x-page-header :title="$heading" breadcrumb="Projects"
            lead="New launches from trusted builders across Pakistan. Filter by city, property type and stage, or explore them on the map." />

        {{-- ───────────── Filters ───────────── --}}
        <form method="GET" action="{{ route('projects.index') }}" role="search" aria-label="Filter projects"
            x-data="{ filters: false, status: @js($filters['status'] ?? ''), wide: matchMedia('(min-width: 768px)'),
                city: @js($filters['city'] ?? ''), area: @js($filters['area'] ?? ''), areas: @js($areaOptions),
                cityNames: @js(collect($areaOptions)->keys()->mapWithKeys(fn ($c) => [$c => \App\Support\Projects::cityName($c)])) }" @keydown.escape.window="filters = false" x-effect="document.documentElement.classList.toggle('overflow-hidden', filters)"
            class="z-30 border-b border-line bg-white md:sticky md:top-16 md:bg-white/95 md:backdrop-blur-md">
            <input type="hidden" name="view" value="map" :disabled="view !== 'map'">
            {{-- The stage: from the desktop tabs (links) or the phone panel's chips; left out of the URL when it's "All" --}}
            <input type="hidden" name="status" value="{{ $filters['status'] }}" :value="status" :disabled="! status" @disabled(! $filters['status'])>

            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-2.5 md:grid-cols-12">
                    <label class="relative col-span-2 md:col-span-12 lg:col-span-4">
                        <span class="sr-only">Search projects</span>
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search project, builder or area"
                            class="w-full rounded-xl border border-line bg-white py-2.5 pe-3.5 ps-10 text-sm text-ink transition-all duration-200 placeholder:text-muted hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5">
                    </label>
                    {{-- Phones: these filters open in a bottom panel. From 768px the wrapper dissolves (md:contents) into the bar --}}
                    <div x-show="filters" x-transition.opacity @click="filters = false" class="fixed inset-0 z-[70] bg-ink/50 md:hidden" x-cloak></div>
                    <div id="project-filters" role="dialog" aria-label="Filters" x-trap.inert="filters"
                        class="fixed inset-x-0 bottom-0 z-[71] max-h-[90svh] space-y-5 overflow-y-auto md:space-y-0 rounded-t-[1.75rem] bg-white px-5 pt-3 shadow-2xl transition-[translate,visibility] duration-500 ease-out-expo md:visible md:contents md:translate-y-0"
                        :class="filters ? 'visible translate-y-0' : 'invisible translate-y-full'">
                        {{-- Phone sheet header: grab handle, title, close --}}
                        <div class="md:hidden">
                            <span class="mx-auto block h-1.5 w-12 rounded-full bg-line" aria-hidden="true"></span>
                            <div class="mt-4 flex items-center justify-between border-b border-line pb-4">
                            <p class="text-2xl font-bold text-ink">Filters</p>
                            <button type="button" @click="filters = false" aria-label="Close filters"
                                class="hit-area grid h-11 w-11 place-items-center rounded-full bg-mist text-ink transition-colors hover:bg-line">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                            </button>
                            </div>
                        </div>
                        <div class="border-b border-line pb-5 md:hidden">
                            <p class="mb-3 text-base font-bold text-ink">Stage</p>
                            <ul class="flex flex-wrap gap-1.5 min-[360px]:gap-2">
                                @foreach ($stageLinks as $value => $stage)
                                    <li>
                                        <button type="button" @click="status = @js((string) $value)" :aria-pressed="status === @js((string) $value)"
                                            class="hit-area inline-flex min-h-10 items-center whitespace-nowrap rounded-full border px-2 text-[13px] transition-colors min-[360px]:px-2.5 min-[360px]:text-sm min-[400px]:px-4"
                                            :class="status === @js((string) $value) ? 'border-brand bg-brand font-bold text-white' : 'border-line font-medium text-ink hover:border-ink'">
                                            {{-- One row on phones: "All stages" reads "All" (the Stage heading says the rest) --}}
                                            @if ($stage['label'] === 'All stages')
                                                All<span class="sr-only">&nbsp;stages</span>
                                            @else
                                                {{ $stage['label'] }}
                                            @endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <label class="block md:col-span-3 lg:col-span-2">
                            <span class="mb-2.5 block text-base font-bold text-ink md:sr-only">City</span>
                            <span class="relative block">
                                <select x-combo name="city" x-model="city" @change="area = ''; $el.form.elements.area.value = ''; wide.matches && $el.form.requestSubmit()" class="{{ $selectClass }}">
                                    <option value="">All cities</option>
                                    @foreach ($cities as $slug => $name)
                                        <option value="{{ $slug }}" @selected($filters['city'] === $slug)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                {!! $chevron !!}
                            </span>
                        </label>
                        {{-- Area: every area with a listed project, grouped by city; only the chosen city's areas once a city is picked --}}
                        <label class="block md:col-span-3 lg:col-span-2">
                            <span class="mb-2.5 block text-base font-bold text-ink md:sr-only">Area</span>
                            <span class="relative block">
                                <select x-combo name="area" x-model="area" @change="wide.matches && $el.form.requestSubmit()" class="{{ $selectClass }}"
                                    x-init="$el.querySelectorAll('[data-no-js]').forEach((group) => group.remove())">
                                    <option value="">All areas</option>
                                    {{-- Without JavaScript: all areas by city. Alpine replaces these with the list below --}}
                                    @foreach ($areaOptions as $citySlug => $cityAreas)
                                        <optgroup label="{{ \App\Support\Projects::cityName($citySlug) }}" data-no-js>
                                            @foreach ($cityAreas as $areaSlug => $areaLabel)
                                                <option value="{{ $areaSlug }}" @selected($filters['area'] === $areaSlug)>{{ $areaLabel }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                    <template x-for="[group, list] in Object.entries(city ? { [city]: areas[city] ?? {} } : areas)" :key="group">
                                        <optgroup :label="cityNames[group]">
                                            <template x-for="[slug, name] in Object.entries(list)" :key="slug">
                                                <option :value="slug" x-text="name" :selected="slug === area"></option>
                                            </template>
                                        </optgroup>
                                    </template>
                                </select>
                                {!! $chevron !!}
                            </span>
                        </label>
                        <label class="block md:col-span-3 lg:col-span-2">
                            <span class="mb-2.5 block text-base font-bold text-ink md:sr-only">Property type</span>
                            <span class="relative block">
                                <select x-combo name="type" @change="wide.matches && $el.form.requestSubmit()" class="{{ $selectClass }}">
                                    <option value="">All types</option>
                                    @foreach ($types as $value => $label)
                                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                {!! $chevron !!}
                            </span>
                        </label>
                        <label class="block md:col-span-3 lg:col-span-2">
                            <span class="mb-2.5 block text-base font-bold text-ink md:sr-only">Developer</span>
                            <span class="relative block">
                                <select x-combo name="developer" @change="wide.matches && $el.form.requestSubmit()" class="{{ $selectClass }}">
                                    <option value="">All developers</option>
                                    @foreach ($developerOptions as $developer)
                                        <option value="{{ $developer['slug'] }}" @selected($filters['developer'] === $developer['slug'])>{{ $developer['name'] }}</option>
                                    @endforeach
                                </select>
                                {!! $chevron !!}
                            </span>
                        </label>
                        <label class="block md:hidden">
                            <span class="mb-2.5 block text-base font-bold text-ink">Sort</span>
                            <span class="relative block">
                                <select x-combo name="sort" :disabled="wide.matches" class="{{ $selectClass }}">
                                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                                    <option value="name" @selected($filters['sort'] === 'name')>Name A to Z</option>
                                </select>
                                {!! $chevron !!}
                            </span>
                        </label>
                        <div class="sticky bottom-0 -mx-5 flex items-center gap-4 bg-white px-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-3 md:hidden">
                            @if ($active)
                                <a href="{{ route('projects.index', $view === 'map' ? ['view' => 'map'] : []) }}" class="hit-area shrink-0 text-base font-semibold text-brand">Clear all</a>
                            @endif
                            <button type="submit" class="btn btn-primary h-14 flex-1 text-base">Show projects</button>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <button type="button" @click="filters = true" aria-controls="project-filters" :aria-expanded="filters"
                        class="relative inline-flex h-11 items-center gap-2 rounded-full border border-line bg-white px-4 text-sm font-semibold text-ink md:hidden">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round"/></svg>
                        Filters
                        @if ($hasFilters)
                            <span class="h-2 w-2 rounded-full bg-brand"></span><span class="sr-only">(some applied)</span>
                        @endif
                    </button>

                    {{-- Status / handover (phones: inside the filter panel) --}}
                    <div class="scrollbar-none hidden overflow-x-auto md:block">
                        <ul class="inline-flex gap-1.5 rounded-full bg-mist p-1" aria-label="Stage">
                            @foreach ($stageLinks as $stage)
                                <li>
                                    <a href="{{ $stage['href'] }}" @if ($stage['current']) aria-current="true" @endif
                                        @class([
                                            'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-semibold transition-all duration-300',
                                            'bg-ink text-white shadow' => $stage['current'],
                                            'text-ink-soft hover:bg-white hover:text-ink' => ! $stage['current'],
                                        ])>
                                        {{ $stage['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Sort (from 768px; on phones it's in the filter panel) --}}
                    <label class="relative ms-auto hidden shrink-0 md:block">
                        <span class="sr-only">Sort</span>
                        <select x-combo name="sort" :disabled="! wide.matches" @change="$el.form.requestSubmit()"
                            class="cursor-pointer appearance-none rounded-full border border-line bg-white py-2 pe-9 ps-4 text-sm font-semibold text-ink transition-colors hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5">
                            <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                            <option value="name" @selected($filters['sort'] === 'name')>Name A to Z</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </label>

                    {{-- Grid / map --}}
                    <div class="relative inline-flex shrink-0 rounded-full border border-line bg-white p-1" role="group" aria-label="View">
                        <span class="pointer-events-none absolute inset-y-1 w-[calc(50%-4px)] rounded-full bg-brand shadow-[0_8px_18px_-8px_rgba(220,26,33,.9)] transition-transform duration-500 ease-out-expo"
                            :class="view === 'map' ? 'translate-x-full' : 'translate-x-0'"></span>
                        @foreach (['grid' => 'Grid', 'map' => 'Map'] as $mode => $label)
                            <button type="button" @click="view = '{{ $mode }}'" :aria-pressed="view === '{{ $mode }}'"
                                class="hit-area relative z-10 inline-flex w-[4.5rem] items-center justify-center gap-1.5 rounded-full py-1.5 lg:w-24 lg:gap-2 text-sm font-semibold transition-colors duration-300"
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
            </div>
        </form>

        {{-- ───────────── Results ───────────── --}}
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="hidden" data-results="{{ $projects->total() }}"></span>
                @if ($active)
                    <span class="me-1 text-sm text-muted">Filtered by</span>
                @endif
                @foreach ($active as $key => $label)
                    <a href="{{ $without($key) }}" class="hit-area group inline-flex items-center gap-1.5 rounded-full border border-line bg-white px-3 py-1 text-xs font-semibold text-ink transition-colors hover:border-brand hover:text-brand">
                        {{ $label }}<span class="sr-only">, remove filter</span>
                        <svg class="h-3 w-3 transition-transform duration-300 group-hover:rotate-90" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 5l10 10M15 5 5 15" stroke-linecap="round"/></svg>
                    </a>
                @endforeach
                @if ($active)
                    <a href="{{ route('projects.index', $view === 'map' ? ['view' => 'map'] : []) }}"
                        class="hit-area inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand transition-colors hover:bg-brand hover:text-white">Clear all</a>
                @endif
            </div>

            @if ($projects->total() === 0)
                <div class="mt-10 rounded-[18px] border border-dashed border-line px-6 py-16 text-center">
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-50 text-brand">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                    </span>
                    @if ($cityName && count($active) === 1)
                        {{-- A city we cover but with nothing listed yet: offer alerts for that city instead of a dead end --}}
                        <h2 class="mt-5 text-xl font-bold text-ink">No projects in {{ $cityName }} yet</h2>
                        <p class="mx-auto mt-2 max-w-md text-sm text-muted">New launches in {{ $cityName }} are on the way. Leave your details and we’ll tell you as soon as one is listed.</p>
                        <div class="mx-auto mt-6 max-w-lg text-left" x-data="launchAlerts">
                            <p x-cloak x-show="done" role="status" class="rounded-xl bg-brand-50 px-5 py-4 text-center text-sm font-semibold text-ink">You’re on the list. We’ll let you know when {{ $cityName }} projects launch.</p>
                            <form x-show="! done" method="POST" action="{{ route('alerts.store') }}" @submit.prevent="submit($el)" novalidate class="grid gap-2.5 sm:grid-cols-[1fr_1fr_auto]">
                                @csrf
                                <input type="hidden" name="city" value="{{ $filters['city'] }}">
                                <div class="absolute -left-[9999px]" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                                <label><span class="sr-only">Email address</span>
                                    <input type="email" name="email" required autocomplete="email" placeholder="Email address" class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm text-ink placeholder:text-muted focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5"></label>
                                <label><span class="sr-only">Phone number</span>
                                    <input type="tel" name="phone" required autocomplete="tel" inputmode="tel" placeholder="Phone number" class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm text-ink placeholder:text-muted focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5"></label>
                                <button type="submit" :disabled="sending" class="btn btn-primary btn-sm disabled:opacity-75">
                                    <span x-text="sending ? 'Saving…' : 'Notify me'">Notify me</span>
                                </button>
                                <p x-show="error" x-text="error" x-cloak class="text-sm font-medium text-brand sm:col-span-3" role="alert"></p>
                            </form>
                            <p class="mt-4 text-center text-sm"><a href="{{ route('projects.index') }}" class="font-semibold text-ink underline decoration-brand decoration-2 underline-offset-4">See all projects across Pakistan</a></p>
                        </div>
                    @else
                        <h2 class="mt-5 text-xl font-bold text-ink">No projects match these filters</h2>
                        <p class="mt-2 text-sm text-muted">Try another city or stage, or ask our advisors about upcoming launches.</p>
                        <div class="mt-6 flex flex-wrap justify-center gap-2">
                            <a href="{{ route('projects.index') }}" class="btn btn-dark btn-sm">Show all projects</a>
                            <a href="{{ route('contact') }}" class="btn btn-outline btn-sm">Talk to an advisor</a>
                        </div>
                    @endif
                </div>
            @else
                {{-- Grid --}}
                <div x-show="view === 'grid'" @if ($view === 'map') x-cloak @endif
                    x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                    {{-- grid-cols-1 = minmax(0, 1fr): a long one-line location can never push the column wider than the screen --}}
                    <div class="mt-8 grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 sm:gap-y-8 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($projects as $project)
                            <x-reveal :delay="($loop->index % 4) * 70" class="flex">
                                <x-project-card :project="$project" :developer="$developers->get($project['developer'])" compact class="w-full" />
                            </x-reveal>
                        @endforeach
                    </div>

                    @if ($projects->lastPage() > 1)
                        <nav aria-label="Pagination" class="mt-12 flex items-center justify-center gap-1.5">
                            <a href="{{ $projects->previousPageUrl() ?? '#' }}" aria-label="Previous page"
                                @class(['grid h-10 w-10 place-items-center rounded-full border border-line transition-colors', 'pointer-events-none opacity-35' => $projects->onFirstPage(), 'hover:border-ink' => ! $projects->onFirstPage()])>
                                <svg class="h-4 w-4 rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                            @php
                                // First, last and two either side of the current page; "…" fills the gaps (P9)
                                [$current, $last] = [$projects->currentPage(), $projects->lastPage()];
                                $pageNumbers = collect(range(1, $last))->filter(fn ($n) => $n === 1 || $n === $last || abs($n - $current) <= 2)->values();
                            @endphp
                            @foreach ($pageNumbers as $i => $number)
                                @if ($i > 0 && $number - $pageNumbers[$i - 1] > 1)
                                    <span class="grid h-10 min-w-6 place-items-center text-sm text-muted" aria-hidden="true">…</span>
                                @endif
                                @php($url = $projects->url($number))
                                <a href="{{ $url }}" @if ($number === $projects->currentPage()) aria-current="page" @endif
                                    @class([
                                        'grid h-10 min-w-10 place-items-center rounded-full px-3 text-sm font-semibold transition-colors',
                                        'bg-ink text-white' => $number === $projects->currentPage(),
                                        'text-ink-soft hover:bg-mist hover:text-ink' => $number !== $projects->currentPage(),
                                    ])>{{ $number }}</a>
                            @endforeach
                            <a href="{{ $projects->nextPageUrl() ?? '#' }}" aria-label="Next page"
                                @class(['grid h-10 w-10 place-items-center rounded-full border border-line transition-colors', 'pointer-events-none opacity-35' => ! $projects->hasMorePages(), 'hover:border-ink' => $projects->hasMorePages()])>
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </nav>
                    @endif
                </div>

                {{-- Map --}}
                <div x-show="view === 'map'" @if ($view !== 'map') x-cloak @endif
                    x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    class="mt-6">
                    <div class="relative isolate overflow-hidden rounded-[18px] border border-line shadow-[0_30px_60px_-40px_rgba(20,20,20,.45)]">
                        {{-- svh: a fixed height that does not change when the phone browser bar shows or hides while scrolling --}}
                        <div x-ref="map" data-style="{{ config('site.map_style') }}" class="h-[82svh] min-h-[30rem] max-h-[54rem] w-full bg-mist" role="region" aria-label="Map of projects"></div>
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
                        <p class="pointer-events-none absolute left-4 top-4 z-[500] rounded-full bg-white/95 px-4 py-2 text-xs font-semibold text-ink shadow-lg">
                            <span class="text-brand">●</span> Projects on the map
                        </p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
