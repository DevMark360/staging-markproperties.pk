
@php
    $headline = [["Pakistan's", false], ['newest', false], ['projects,', false], ['one', true], ['trusted', true], ['partner.', false]];
    $stages = ['' => 'All Projects', ...$statuses];
@endphp

<x-layouts.app title="New Real Estate Projects in Pakistan" :overlay-header="true"
    description="Explore pre-launch and under-construction residential and commercial projects from leading builders across Pakistan with Mark Properties.">

    {{-- ───────────── Hero ───────────── --}}
    <section x-data="heroSlider({{ $featured->count() }})"
        class="relative isolate flex min-h-[100svh] flex-col overflow-hidden bg-ink text-white lg:min-h-[max(100svh,780px)]">

        {{-- Background slides --}}
        <div class="absolute inset-0 -z-10" aria-hidden="true">
            @foreach ($featured as $k => $project)
                <div class="absolute inset-0 transition-opacity duration-[1400ms] ease-out {{ $k === 0 ? 'opacity-100' : 'opacity-0' }}"
                    :class="i === {{ $k }} ? 'opacity-100' : 'opacity-0'">
                    {{-- Only the first two photos load with the page; each later one just before its turn (H2) --}}
                    @if ($k < 2)
                        <img src="{{ \App\Support\Projects::imageUrl($project['hero'], 'hero') }}" alt="" @if ($k === 0) fetchpriority="high" @endif
                    @else
                        <img :src="ready[{{ $k }}] ? @js(\App\Support\Projects::imageUrl($project['hero'], 'hero')) : null" alt=""
                    @endif
                        class="h-full w-full scale-105 object-cover transition-transform duration-[8000ms] ease-out"
                        :class="i === {{ $k }} && 'scale-100!'">
                </div>
            @endforeach
            <div class="absolute inset-0 bg-linear-to-r from-ink/95 via-ink/70 to-ink/20"></div>
            <div class="absolute inset-0 bg-linear-to-t from-ink via-ink/10 to-ink/50"></div>
            <div class="absolute inset-0 bg-[radial-gradient(50%_60%_at_100%_100%,rgba(220,26,33,.22),transparent_70%)]"></div>
        </div>

        <div class="mx-auto grid w-full max-w-7xl flex-1 grid-cols-1 items-end gap-10 px-4 pb-10 pt-28 sm:px-6 sm:pb-14 sm:pt-32 lg:grid-cols-12 lg:gap-12 lg:px-8 lg:pb-16">

            {{-- Copy, stages + search --}}
            <div class="lg:col-span-7">
                <p class="inline-flex animate-rise items-center gap-2.5 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-semibold text-white/80 backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-brand"></span>
                    </span>
                    Off-plan projects across Pakistan
                </p>

                <h1 class="mt-5 text-display font-extrabold leading-[1.06] tracking-tight sm:mt-6">
                    @foreach ($headline as [$word, $accent])
                        <span class="inline-block overflow-hidden pb-1 align-bottom"><span class="inline-block animate-word {{ $accent ? 'text-brand' : '' }}" style="animation-delay: {{ 120 + $loop->index * 70 }}ms">{{ $word }}</span></span>
                    @endforeach
                </h1>

                <p class="mt-4 max-w-xl animate-rise text-lead leading-relaxed text-white/70 [animation-delay:500ms] sm:mt-6">
                    Explore pre-launch and under-construction projects from leading builders across Pakistan.
                </p>

                <form action="{{ route('projects.index') }}" method="GET" role="search" class="mt-9 animate-rise [animation-delay:620ms]">
                    {{-- Stage tabs --}}
                    <div class="scrollbar-none -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                        <div x-data="segmented('')" @mouseleave="highlight(null)" role="radiogroup" aria-label="Project stage"
                            @keydown.arrow-right.prevent="step(1)" @keydown.arrow-down.prevent="step(1)" @keydown.arrow-left.prevent="step(-1)" @keydown.arrow-up.prevent="step(-1)"
                            class="relative inline-flex rounded-full border border-white/15 bg-white/10 p-1 backdrop-blur-md">
                            <span class="pointer-events-none absolute inset-y-1 rounded-full bg-white/10 transition-all duration-300 ease-out-expo"
                                :class="hover ? 'opacity-100' : 'opacity-0'"
                                :style="hover && { left: hover.left + 'px', width: hover.width + 'px' }"></span>
                            <span class="pointer-events-none absolute inset-y-1 rounded-full bg-brand shadow-[0_8px_20px_-8px_rgba(220,26,33,.9)] transition-all duration-500 ease-out-expo"
                                :style="{ left: indicator.left + 'px', width: indicator.width + 'px' }"></span>
                            <input type="hidden" name="status" :value="value">
                            @foreach ($stages as $value => $label)
                                <button type="button" role="radio" data-value="{{ $value }}"
                                    @click="select('{{ $value }}')" @mouseenter="highlight($el)"
                                    :aria-checked="value === '{{ $value }}'" :tabindex="value === '{{ $value }}' ? 0 : -1"
                                    class="hit-area relative z-10 whitespace-nowrap rounded-full px-3 py-2 text-[13px] font-semibold transition-colors duration-300 sm:px-5 sm:text-sm"
                                    :class="value === '{{ $value }}' ? 'text-white' : 'text-white/70 hover:text-white'">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Search --}}
                    <div x-data="{ q: '', h: 0, hints: @js($searchHints) }" x-init="hints.length > 1 && setInterval(() => q === '' && (h = (h + 1) % hints.length), 2600)"
                        class="group/search mt-3 flex items-center gap-2 rounded-2xl bg-white p-2 shadow-[0_30px_60px_-25px_rgba(0,0,0,.7)] ring-1 ring-white/20 transition-shadow duration-300 focus-within:ring-4 focus-within:ring-brand/40">
                        <label class="relative flex min-w-0 flex-1 items-center gap-3 ps-3">
                            <svg class="h-5 w-5 shrink-0 text-muted transition-colors duration-300 group-focus-within/search:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                            <span class="sr-only">Search by project, builder or area</span>
                            <input type="search" name="q" x-model="q" autocomplete="off"
                                class="w-full bg-transparent py-3 text-sm text-ink focus:outline-none sm:text-base">
                            {{-- Rotating hint, shown while the field is empty --}}
                            <span x-show="q === ''" class="pointer-events-none absolute inset-y-0 left-11 right-0 flex items-center overflow-hidden text-sm text-muted sm:text-base">
                                <span class="shrink-0">Search&nbsp;</span>
                                <span class="relative h-6 flex-1">
                                    <template x-for="(hint, k) in hints" :key="k">
                                        <span x-show="h === k"
                                            x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition duration-300" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-3"
                                            class="absolute inset-0 truncate font-medium text-ink/60" x-text="'“' + hint + '”'"></span>
                                    </template>
                                </span>
                            </span>
                        </label>
                        <label class="relative hidden shrink-0 border-s border-line sm:block">
                            <span class="sr-only">City</span>
                            <select x-combo name="city" class="cursor-pointer appearance-none bg-transparent py-3 pe-9 ps-4 text-sm font-semibold text-ink focus:outline-none">
                                <option value="">All Pakistan</option>
                                @foreach ($cities as $slug => $name)
                                    <option value="{{ $slug }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </label>
                        <button type="submit" class="hit-area group/btn inline-flex shrink-0 items-center gap-2 rounded-xl bg-brand px-5 py-3 text-sm font-semibold text-white transition-all duration-300 hover:bg-brand-700 active:scale-[.97] sm:px-7">
                            <span class="hidden sm:inline">Search</span>
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover/btn:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </form>

            </div>

            {{-- Featured launch card --}}
            @if ($featured->isNotEmpty())
                <div class="animate-rise [animation-delay:800ms] lg:col-span-5 lg:justify-self-end">
                    {{-- Pauses while the pointer or keyboard focus is on the card, or when stopped with the button (H1) --}}
                    <div @mouseenter="paused = true" @mouseleave="paused = false" @focusin="paused = true" @focusout="paused = $el.contains($event.relatedTarget)"
                        @pointermove="const r = $el.getBoundingClientRect(); $el.style.setProperty('--mx', ($event.clientX - r.left) + 'px'); $el.style.setProperty('--my', ($event.clientY - r.top) + 'px')"
                        class="group/card relative w-full overflow-hidden rounded-2xl border border-white/15 bg-ink/45 p-4 shadow-[0_40px_80px_-30px_rgba(0,0,0,.9)] backdrop-blur-xl sm:rounded-3xl sm:p-7 lg:w-[400px]">
                        {{-- Cursor spotlight --}}
                        <span class="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 group-hover/card:opacity-100 bg-[radial-gradient(320px_circle_at_var(--mx)_var(--my),rgba(220,26,33,.22),transparent_65%)]" aria-hidden="true"></span>
                        <span class="absolute inset-y-4 left-0 w-[3px] rounded-full bg-brand sm:inset-y-6" aria-hidden="true"></span>

                        <div class="relative flex items-center justify-between">
                            <p class="text-xs font-semibold text-white/60 sm:text-sm">Featured launch</p>
                            <p class="text-xs font-semibold tabular-nums text-white/60"><span x-text="String(i + 1).padStart(2, '0')">01</span> / {{ str_pad($featured->count(), 2, '0', STR_PAD_LEFT) }}</p>
                        </div>

                        {{-- Stacked slides; grid keeps the card at the tallest slide's height --}}
                        <div class="relative mt-2 grid sm:mt-4">
                            @foreach ($featured as $k => $project)
                                @php $status = \App\Support\Projects::stageName($project['status']); @endphp
                                <div x-show="i === {{ $k }}" @if ($k) x-cloak @endif
                                    x-transition:enter="transition duration-700 ease-out-expo delay-150" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                    class="col-start-1 row-start-1">
                                    <h2 class="text-lg font-bold leading-tight sm:text-2xl">{{ $project['name'] }}</h2>
                                    @if ($developer = $developers->get($project['developer']))
                                        <p class="mt-0.5 text-[13px] text-white/60 sm:mt-1 sm:text-sm">by {{ $developer['name'] }}</p>
                                    @endif

                                    <dl class="mt-3 grid grid-cols-2 gap-3 border-t border-white/10 pt-3 sm:mt-5 sm:gap-4 sm:pt-5">
                                        <div>
                                            <dt class="text-xs text-white/50">Location</dt>
                                            <dd class="mt-0.5 text-[13px] font-semibold leading-snug sm:mt-1 sm:text-sm">{{ $project['area'] }}, {{ \App\Support\Projects::cityName($project['city']) }}</dd>
                                        </div>
                                        @if ($status)
                                            <div>
                                                <dt class="text-xs text-white/50">Stage</dt>
                                                <dd class="mt-0.5 text-[13px] font-semibold leading-snug sm:mt-1 sm:text-sm">{{ $status }}</dd>
                                            </div>
                                        @endif
                                    </dl>

                                    <a href="{{ route('projects.show', $project['slug']) }}"
                                        class="hit-area group/link mt-3 inline-flex items-center gap-2 border-b border-white/30 pb-1 text-[13px] font-semibold sm:mt-6 sm:text-sm transition-colors duration-300 hover:border-brand">
                                        See the project
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        {{-- Progress + controls --}}
                        <div class="relative mt-5 flex items-center gap-4 sm:mt-7">
                            <div class="flex flex-1 gap-1.5">
                                @foreach ($featured as $k => $project)
                                    <button type="button" @click="i = {{ $k }}" aria-label="Show {{ $project['name'] }}" class="hit-area group/bar h-6 flex-1 py-2.5">
                                        <span class="block h-1 overflow-hidden rounded-full bg-white/15 transition-colors group-hover/bar:bg-white/30">
                                            <span class="block h-full rounded-full bg-brand"
                                                :class="i === {{ $k }} ? (autoplay ? 'animate-[fill_6s_linear_forwards]' : 'w-full') : 'w-0'"
                                                :style="(paused || stopped) && { animationPlayState: 'paused' }"
                                                @animationend="next()"></span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            <div class="flex gap-1.5">
                                <button type="button" x-show="autoplay" @click="stopped = ! stopped" :aria-label="stopped ? 'Play the slideshow' : 'Pause the slideshow'" :aria-pressed="stopped"
                                    class="hit-area grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/80 transition-all duration-300 hover:border-white hover:text-white active:scale-90">
                                    <svg x-show="! stopped" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><rect x="5" y="4" width="3.5" height="12" rx="1"/><rect x="11.5" y="4" width="3.5" height="12" rx="1"/></svg>
                                    <svg x-show="stopped" x-cloak class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6 4.5v11a1 1 0 0 0 1.5.86l9-5.5a1 1 0 0 0 0-1.72l-9-5.5A1 1 0 0 0 6 4.5Z"/></svg>
                                </button>
                                <button type="button" @click="prev()" aria-label="Previous project"
                                    class="hit-area grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/80 transition-all duration-300 hover:border-white hover:text-white active:scale-90">
                                    <svg class="h-4 w-4 rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <button type="button" @click="next()" aria-label="Next project"
                                    class="hit-area grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/80 transition-all duration-300 hover:border-white hover:text-white active:scale-90">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ───────────── Trusted developers ───────────── --}}
    @php
        $partners = \App\Support\BuilderStrip::for('home'); // partner builders (About and Partner show others)
    @endphp
    @if ($partners->isNotEmpty())
        <section class="py-section-sm bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-reveal>
                    <x-section-header size="lg" title="Partnered with Pakistan's leading builders"
                        lead="We market new projects for builders across Pakistan, from established groups to new names on the skyline."
                        action="All developers" :href="route('developers.index')" />
                </x-reveal>

                {{-- Partner developer logos --}}
                <ul class="mt-section-body grid grid-cols-4 gap-2 sm:gap-4 xl:grid-cols-8">
                    @foreach ($partners as $developer)
                        <li>
                            <x-reveal :delay="$loop->index * 60" class="h-full">
                                <a href="{{ route('developers.show', $developer['slug']) }}" title="{{ $developer['name'] }}"
                                    class="group relative grid h-20 place-items-center overflow-hidden rounded-xl bg-mist px-2 sm:h-28 sm:rounded-2xl sm:px-5 transition-all duration-500 ease-out-expo hover:-translate-y-1 hover:bg-white hover:shadow-[0_22px_40px_-22px_rgba(20,20,20,.35)] hover:ring-1 hover:ring-line">
                                    @if ($developer['logo'])
                                        {{-- Monochrome logo, crossfading to the original colours on hover --}}
                                        <x-builder-logo :developer="$developer" size="clamp(2.2rem, 1.6rem + 2.1vw, 3.7rem)" class="group-hover:-translate-y-2" />
                                    @else
                                        <span class="text-center text-sm font-bold text-ink">{{ $developer['name'] }}</span>
                                    @endif
                                    <span class="absolute inset-x-2 bottom-2 translate-y-2 truncate text-center text-xs font-semibold text-ink-soft opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">{{ $developer['name'] }}</span>
                                </a>
                            </x-reveal>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ───────────── Trending projects ───────────── --}}
    @if ($trending->isNotEmpty())
        <section x-data="carousel" class="py-section relative overflow-hidden bg-ink text-white">
            <div class="pointer-events-none absolute -right-40 top-10 h-[26rem] w-[26rem] rounded-full bg-brand/10 blur-3xl" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-reveal>
                    <x-section-header :dark="true" size="lg" title="Trending projects across Pakistan"
                        lead="The off-plan projects buyers are exploring most on AbadRaho right now."
                        action="All projects" :href="route('projects.index')" />
                </x-reveal>

                <div x-ref="track" @scroll.passive="update()" @resize.window.debounce="update()"
                    class="scrollbar-none mt-section-body -mx-4 flex items-start snap-x sm:items-stretch snap-mandatory gap-5 overflow-x-auto scroll-px-4 px-4 pb-8 pt-2 sm:-mx-6 sm:scroll-px-6 sm:px-6 lg:mx-0 lg:scroll-px-0 lg:px-0">
                    @foreach ($trending as $project)
                        <div class="flex w-[82%] shrink-0 snap-start sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] xl:w-[calc((100%-3.75rem)/4)]">
                            <x-project-card :project="$project" :developer="$developers->get($project['developer'])" :rank="$loop->iteration <= 3 ? $loop->iteration : null" class="w-full" />
                        </div>
                    @endforeach
                </div>

                {{-- Scroll progress + arrows --}}
                <div class="flex items-center gap-4">
                    <div class="h-1 flex-1 overflow-hidden rounded-full bg-white/15 transition-opacity duration-300" :class="atStart && atEnd && 'opacity-0'" aria-hidden="true">
                        <div class="h-full rounded-full bg-brand transition-[width] duration-300 ease-out" :style="{ width: Math.max(progress, 0.08) * 100 + '%' }"></div>
                    </div>
                    <div class="hidden items-center gap-2 sm:flex">
                        <button type="button" @click="slide(-1)" :disabled="atStart" aria-label="Previous projects"
                            class="grid h-11 w-11 place-items-center rounded-full border border-white/20 text-white transition-all duration-300 hover:border-white hover:bg-white/10 active:scale-90 disabled:cursor-not-allowed disabled:opacity-30">
                            <svg class="h-4 w-4 rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" @click="slide(1)" :disabled="atEnd" aria-label="Next projects"
                            class="grid h-11 w-11 place-items-center rounded-full border border-white/20 text-white transition-all duration-300 hover:border-white hover:bg-white/10 active:scale-90 disabled:cursor-not-allowed disabled:opacity-30">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ───────────── Latest launches ───────────── --}}
    @if ($launches->isNotEmpty())
        @php
            $spotlight = $launches->first();
            $spotlightDeveloper = $developers->get($spotlight['developer']);
        @endphp
        <section class="py-section overflow-hidden bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-reveal>
                    <x-section-header size="lg" title="New pre-launch projects in Pakistan"
                        lead="Be early on the newest launches from trusted builders, before construction begins."
                        action="All pre-launch" :href="route('projects.index', ['status' => \App\Models\Stage::PRE_LAUNCH])" />
                </x-reveal>

                <div class="mt-section-body grid gap-6 lg:grid-cols-12">
                    {{-- Newest launch, spotlighted --}}
                    <x-reveal class="lg:col-span-5">
                        <article
                            @pointermove="const r = $el.getBoundingClientRect(); $el.style.setProperty('--mx', ($event.clientX - r.left) + 'px'); $el.style.setProperty('--my', ($event.clientY - r.top) + 'px')"
                            class="group relative isolate flex h-full min-h-[21rem] flex-col justify-end overflow-hidden rounded-3xl bg-ink p-5 text-white sm:min-h-[30rem] sm:rounded-[2rem] sm:p-9">
                            @if ($spotlight['image'])
                                <img src="{{ \App\Support\Projects::imageUrl($spotlight['image']) }}" alt="{{ $spotlight['name'] }}" width="800" height="600" loading="lazy"
                                    class="absolute inset-0 -z-10 h-full w-full object-cover transition-transform duration-[1200ms] ease-out-expo group-hover:scale-105">
                            @endif
                            <span class="absolute inset-0 -z-10 bg-linear-to-t from-ink via-ink/55 to-ink/5" aria-hidden="true"></span>
                            <span class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(420px_circle_at_var(--mx)_var(--my),rgba(220,26,33,.25),transparent_65%)] opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></span>

                            <span class="absolute left-7 top-7 inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-1.5 text-xs font-semibold text-ink sm:left-9 sm:top-9">
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-brand"></span>
                                </span>
                                Newest launch
                            </span>

                            @if ($spotlightDeveloper)
                                <a href="{{ route('developers.show', $spotlightDeveloper['slug']) }}" class="hit-area flex items-center gap-2.5 self-start text-sm font-semibold text-white/75 transition-colors hover:text-white">
                                    @if ($spotlightDeveloper['logo'])
                                        <img src="{{ \App\Support\Projects::fileUrl($spotlightDeveloper['logo'], 'images/developers') }}" alt="" width="320" height="320" loading="lazy" class="h-8 w-8 rounded-full bg-white object-contain">
                                    @endif
                                    {{ $spotlightDeveloper['name'] }}
                                </a>
                            @endif
                            <h3 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">{{ $spotlight['name'] }}</h3>
                            <p class="mt-2 flex items-center gap-2 text-white/70">
                                <svg class="h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                                {{ $spotlight['area'] }}, {{ \App\Support\Projects::cityName($spotlight['city']) }}
                            </p>

                            <div class="mt-7 flex flex-wrap items-center gap-3">
                                <a href="{{ route('projects.show', $spotlight['slug']) }}" class="btn btn-light">View project</a>
                            </div>
                        </article>
                    </x-reveal>

                    {{-- More launches --}}
                    <x-reveal :delay="120" class="min-w-0 lg:col-span-7">
                        <div x-data="carousel" class="flex h-full flex-col">
                            <div x-ref="track" @scroll.passive="update()" @resize.window.debounce="update()"
                                class="scrollbar-none -mx-4 flex flex-1 items-start snap-x sm:items-stretch snap-mandatory gap-5 overflow-x-auto scroll-px-4 px-4 pb-6 pt-1 sm:-mx-6 sm:scroll-px-6 sm:px-6 lg:mx-0 lg:scroll-px-0 lg:px-0">
                                @foreach ($launches->slice(1) as $project)
                                    <div class="flex w-[82%] shrink-0 snap-start sm:w-[calc((100%-1.25rem)/2)]">
                                        <x-project-card :project="$project" :developer="$developers->get($project['developer'])" class="w-full" />
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="h-1 flex-1 overflow-hidden rounded-full bg-ink/10" aria-hidden="true">
                                    <div class="h-full rounded-full bg-brand transition-[width] duration-300 ease-out" :style="{ width: Math.max(progress, 0.08) * 100 + '%' }"></div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="slide(-1)" :disabled="atStart" aria-label="Previous launches"
                                        class="grid h-11 w-11 place-items-center rounded-full border border-ink/15 bg-white text-ink transition-all duration-300 hover:border-ink active:scale-90 disabled:cursor-not-allowed disabled:opacity-35">
                                        <svg class="h-4 w-4 rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <button type="button" @click="slide(1)" :disabled="atEnd" aria-label="Next launches"
                                        class="grid h-11 w-11 place-items-center rounded-full border border-ink/15 bg-white text-ink transition-all duration-300 hover:border-ink active:scale-90 disabled:cursor-not-allowed disabled:opacity-35">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-reveal>
                </div>
            </div>
        </section>
    @endif

    {{-- ───────────── Locations (light, so the page alternates dark and light sections) ───────────── --}}
    @if ($locations->isNotEmpty())
        <section class="py-section relative overflow-hidden bg-mist">

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-reveal>
                    <x-section-header size="lg" :on-mist="true" title="Where Pakistan is building next"
                        lead="The growth corridors where new off-plan projects are launching, from Karachi's Super Highway to the waterfront at Korangi Creek."
                        action="All areas" :href="route('projects.index')" />
                </x-reveal>

                @php
                    // With 3+ areas: the squeeze carousel below, at every screen size. With 1-2 areas: these cards.
                    $squeeze = $locations->count() >= 3;
                    $squeezeSlides = $locations->map(fn (array $l) => [
                        'name' => $l['name'],
                        'city' => \App\Support\Projects::cityName($l['city']),
                        'description' => $l['description'],
                        'image' => \App\Support\Projects::imageUrl($l['image'], 'locations'),
                        'href' => $l['projects'] > 0 ? route('projects.index', ['city' => $l['city'], 'area' => $l['slug']]) : null,
                    ])->values();
                @endphp

                <div @class([
                    'scrollbar-none mt-section-body -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-px-4 px-4 pb-2 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-4',
                    'hidden!' => $squeeze,
                ])>
                    @foreach ($locations as $location)
                        {{-- Every area switched on for the home page shows; only those with a published project link to it --}}
                        @php $linked = $location['projects'] > 0; @endphp
                        <x-reveal :delay="$loop->index * 110" class="w-[78%] shrink-0 snap-start sm:w-auto">
                            <{{ $linked ? 'a' : 'div' }} @if ($linked) href="{{ route('projects.index', ['city' => $location['city'], 'area' => $location['slug']]) }}" @endif
                                @class([
                                    'relative block aspect-[4/5] overflow-hidden rounded-3xl bg-ink text-white ring-1 ring-ink/5',
                                    'group transition-all duration-500 ease-out-expo hover:-translate-y-1.5 hover:shadow-[0_34px_60px_-28px_rgba(220,26,33,.55)] hover:ring-white/25' => $linked,
                                ])>
                                {{-- The whole photo, never cropped (a wide photo shows complete); a blurred copy fills the rest of the card --}}
                                <img src="{{ \App\Support\Projects::imageUrl($location['image'], 'locations') }}" alt="" aria-hidden="true" loading="lazy"
                                    class="absolute inset-0 h-full w-full scale-110 object-cover opacity-80 blur-2xl">
                                <img src="{{ \App\Support\Projects::imageUrl($location['image'], 'locations') }}" alt="{{ $location['name'] }}, {{ \App\Support\Projects::cityName($location['city']) }}" width="600" height="750" loading="lazy"
                                    class="absolute inset-0 h-full w-full object-contain transition-transform duration-[1200ms] ease-out-expo group-hover:scale-105">
                                <span class="absolute inset-0 bg-linear-to-t from-ink via-ink/45 to-ink/0 transition-opacity duration-500" aria-hidden="true"></span>
                                <span class="absolute inset-0 bg-linear-to-t from-brand/25 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></span>


                                @if ($linked)
                                    <span class="absolute right-5 top-5 grid h-11 w-11 place-items-center rounded-full bg-white/15 text-white ring-1 ring-white/20 backdrop-blur-md transition-all duration-500 ease-out-expo group-hover:rotate-45 group-hover:bg-brand group-hover:ring-brand" aria-hidden="true">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 14 14 6M7 6h7v7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                @endif

                                <span class="absolute inset-x-0 bottom-0 p-6">
                                    <span class="block text-sm text-white/55">{{ \App\Support\Projects::cityName($location['city']) }}</span>
                                    <span class="mt-1 block text-2xl font-bold leading-tight">{{ $location['name'] }}</span>
                                    {{-- Always 3 lines tall (longer text is clipped), so every card's title sits at the same height --}}
                                    <span class="mt-2 line-clamp-3 min-h-[4.875em] text-sm leading-relaxed text-white/70">{{ $location['description'] }}</span>
                                    {{-- Revealed on hover --}}
                                    @if ($linked)
                                        <span class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-500 ease-out-expo group-hover:grid-rows-[1fr]">
                                            <span class="overflow-hidden">
                                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white">
                                                    Explore projects
                                                </span>
                                            </span>
                                        </span>
                                    @endif
                                </span>
                            </{{ $linked ? 'a' : 'div' }}>
                        </x-reveal>
                    @endforeach
                </div>

                @if ($squeeze)
                    {{-- Squeeze carousel: one open area, the next ones narrower, the rest as slats (resources/js/squeeze.js) --}}
                    <x-reveal class="mt-section-body">
                        <div x-data="squeeze(@js($squeezeSlides))" :style="vars()" @mouseleave="hover = -1"
                            class="w-full [container-type:inline-size] [--sq-radius:1.25rem] [--sq-gap:16px] [--sq-h:clamp(14rem,31cqi,24rem)] [--sq-hero:calc(var(--sq-h)*16/9)] max-sm:[--sq-gap:8px] max-sm:[--sq-hero:calc(100cqi*0.62)] max-sm:[--sq-h:calc(var(--sq-hero)*1.25)]">
                            {{-- Phones: a tall open panel about 62% of the width (area photos are tall), smaller gaps --}}

                            <div class="mb-4 flex justify-end gap-2">
                                <button type="button" @click="step(-1)" aria-label="Previous area"
                                    class="grid h-11 w-11 place-items-center rounded-full bg-white text-ink ring-1 ring-ink/10 transition-colors hover:bg-ink hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5 7 10l5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <button type="button" @click="step(1)" aria-label="Next area"
                                    class="grid h-11 w-11 place-items-center rounded-full bg-white text-ink ring-1 ring-ink/10 transition-colors hover:bg-ink hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>

                            <div class="w-full overflow-hidden" style="height: var(--sq-h)">
                                <div role="tablist" aria-label="Areas" class="flex h-full w-max" :style="stripStyle()"
                                    @keydown.right.prevent="step(1)" @keydown.left.prevent="step(-1)"
                                    @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)">
                                    <template x-for="(card, place) in cards" :key="card.key">
                                        <button type="button" role="tab" :aria-selected="col(place) === 0" :tabindex="col(place) === 0 ? 0 : -1"
                                            :aria-label="slides[card.slide].name + ', ' + slides[card.slide].city"
                                            @mousemove="hover = col(place)" @click="pick(place)" :style="cardStyle(place)"
                                            :class="col(place) === 0 && slides[card.slide].href ? 'cursor-pointer' : (col(place) === 0 ? 'cursor-default' : 'cursor-pointer')"
                                            class="relative isolate h-full shrink-0 overflow-hidden bg-ink p-0 outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 focus-visible:ring-offset-mist">
                                            {{-- The whole photo top to bottom (area photos are tall): drawn at the panel's full height and
                                                 centred, so a narrow panel shows its middle; a blurred copy fills any space beside it --}}
                                            <img :src="slides[card.slide].image" alt="" aria-hidden="true" draggable="false" loading="lazy"
                                                class="absolute inset-0 h-full w-full scale-110 object-cover opacity-80 blur-2xl">
                                            <img :src="slides[card.slide].image" alt="" draggable="false" loading="lazy"
                                                class="absolute inset-y-0 left-1/2 h-full w-auto max-w-none -translate-x-1/2">
                                            <span aria-hidden="true"
                                                class="pointer-events-none absolute inset-x-0 bottom-0 bg-linear-to-t from-ink/85 via-ink/40 to-transparent p-4 pt-16 text-start text-white transition-opacity sm:p-6 sm:pt-24"
                                                :style="{ opacity: col(place) === 0 ? 1 : 0, transitionDuration: 'var(--sq-ms)' }">
                                                <span class="block text-sm text-white/60" x-text="slides[card.slide].city"></span>
                                                <span class="text-card-title mt-1 block font-bold leading-tight" x-text="slides[card.slide].name"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- The open area's description and link, cross-fading as it changes --}}
                            <div class="mt-6 grid" aria-live="polite">
                                @foreach ($squeezeSlides as $i => $slide)
                                    <div class="col-start-1 row-start-1 flex flex-col gap-4 transition-opacity md:flex-row md:items-start md:justify-between md:gap-10"
                                        :style="{ opacity: open === {{ $i }} ? 1 : 0, visibility: open === {{ $i }} ? 'visible' : 'hidden', transitionDuration: 'var(--sq-ms)' }"
                                        :aria-hidden="open !== {{ $i }}" @if ($i > 0) style="opacity: 0; visibility: hidden" @endif>
                                        <p class="text-lead max-w-3xl">
                                            <span class="font-semibold text-ink">{{ $slide['name'] }}, {{ $slide['city'] }}.</span>
                                            <span class="text-muted">{{ $slide['description'] }}</span>
                                        </p>
                                        @if ($slide['href'])
                                            <a href="{{ $slide['href'] }}" :tabindex="open === {{ $i }} ? 0 : -1"
                                                class="group inline-flex min-h-11 shrink-0 items-center gap-2 self-start rounded-full bg-brand px-5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                                Explore projects
                                                <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-reveal>
                @endif
            </div>
        </section>
    @endif

    {{-- ───────────── How it works ───────────── --}}
    @php
        $steps = [
            [
                'title' => 'Search projects',
                'text' => 'Filter off-plan projects by city, area, stage and type to build your shortlist.',
                'href' => route('projects.index'),
                'icon' => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2" stroke-linecap="round"/>',
            ],
            [
                'title' => 'Schedule a site visit',
                'text' => 'Book a guided visit with our advisors once you have shortlisted projects worth seeing.',
                'href' => route('contact', ['topic' => 'site-visit']).'#message',
                'icon' => '<rect x="4" y="5" width="16" height="15" rx="2.5"/><path d="M4 10h16M9 3v4M15 3v4" stroke-linecap="round"/><path d="m9.5 15 1.8 1.8 3.4-3.6" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
            [
                'title' => 'Talk to an advisor',
                'text' => 'Get clear answers on developer track records, construction progress and area growth.',
                'href' => route('contact'),
                'icon' => '<path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h7A2.5 2.5 0 0 1 16 6.5v4a2.5 2.5 0 0 1-2.5 2.5H9l-3.5 3v-3A2.5 2.5 0 0 1 4 10.5v-4Z" stroke-linejoin="round"/><path d="M16 9h1.5A2.5 2.5 0 0 1 20 11.5v4a2.5 2.5 0 0 1-1.5 2.3V20l-3-2h-3a2.5 2.5 0 0 1-2.3-1.5" stroke-linejoin="round"/>',
            ],
            [
                'title' => 'Book with confidence',
                'text' => 'Finalise your choice and book directly with the builder, with documented terms and our support.',
                'href' => route('guide'),
                'icon' => '<path d="M12 3.5 5 6v5.5c0 4.2 2.9 7.6 7 9 4.1-1.4 7-4.8 7-9V6l-7-2.5Z" stroke-linejoin="round"/><path d="m9 12 2.2 2.2L15.5 10" stroke-linecap="round" stroke-linejoin="round"/>',
            ],
        ];
    @endphp
    <section class="py-section bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-reveal>
                <x-section-header size="lg" title="How buying off-plan works with us"
                    lead="Four steps, with advisor support at every one, from your first search to booking."
                    action="Buyer’s guide" :href="route('guide')" />
            </x-reveal>

            <ol class="mt-section-body grid gap-3 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4">
                @foreach ($steps as $step)
                    <li>
                        <x-reveal :delay="$loop->index * 110" class="h-full">
                            <a href="{{ $step['href'] }}"
                                @pointermove="const r = $el.getBoundingClientRect(); $el.style.setProperty('--mx', ($event.clientX - r.left) + 'px'); $el.style.setProperty('--my', ($event.clientY - r.top) + 'px')"
                                class="group relative flex h-full flex-col gap-4 overflow-hidden rounded-2xl bg-ink p-5 text-white sm:min-h-[23rem] sm:gap-8 sm:rounded-3xl sm:p-7 transition-all duration-500 ease-out-expo hover:-translate-y-1.5 hover:shadow-[0_34px_60px_-28px_rgba(20,20,20,.75)]">
                                {{-- Cursor spotlight --}}
                                <span class="pointer-events-none absolute inset-0 bg-[radial-gradient(300px_circle_at_var(--mx)_var(--my),rgba(220,26,33,.2),transparent_65%)] opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></span>

                                <span class="relative flex items-start justify-between">
                                    <span class="grid h-11 w-11 place-items-center rounded-full bg-brand text-white shadow-[0_12px_24px_-10px_rgba(220,26,33,.9)] sm:h-14 sm:w-14 transition-transform duration-500 ease-out-expo group-hover:-rotate-6 group-hover:scale-110">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $step['icon'] !!}</svg>
                                    </span>
                                    <span class="text-sm font-semibold tabular-nums text-white/55 transition-colors duration-300 group-hover:text-white/80">{{ sprintf('%02d', $loop->iteration) }}</span>
                                </span>

                                <span class="relative sm:pt-16">
                                    <span class="block text-lg font-bold sm:text-xl">{{ $step['title'] }}</span>
                                    <span class="mt-1.5 block text-sm leading-relaxed text-white/60 sm:mt-3 sm:text-[15px]">{{ $step['text'] }}</span>
                                </span>

                                <span class="relative mt-auto hidden h-11 w-11 place-items-center rounded-full border border-white/20 sm:grid text-white transition-all duration-500 ease-out-expo group-hover:rotate-45 group-hover:border-brand group-hover:bg-brand" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 14 14 6M7 6h7v7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </a>
                        </x-reveal>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ───────────── Blog ───────────── --}}
    @if ($posts->isNotEmpty())
        <section class="py-section border-t border-line bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-reveal>
                    <x-section-header size="lg" title="Guides and tips for buying in Pakistan"
                        lead="Practical advice to help you invest in off-plan property with confidence."
                        action="All articles" :href="route('blog')" />
                </x-reveal>

                {{-- Same card as the blog page --}}
                <div class="mt-section-body grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-reveal :delay="$loop->index * 110" class="h-full">
                            <x-post-card :post="$post" heading="h3" class="h-full" />
                        </x-reveal>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('pages.home.faq')

    @include('pages.home.launch-alerts')

</x-layouts.app>
