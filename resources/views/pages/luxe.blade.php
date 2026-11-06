@php
    // The landing hero has its own photo (not one of the collection tiles or project cards below it)
    $heroImage = $active ? \App\Support\Projects::fileUrl($active['cover'], 'images') : asset('images/hero/luxe.webp');
    $title = $active ? $active['name'].' in The Luxury Collection' : 'The Luxury Collection';
    $headline = $active ? explode(' ', $active['name']) : ['The', 'Luxury', 'Collection'];
@endphp

<x-layouts.app :title="$title" :overlay-header="true" :image="$heroImage"
    :description="$active ? $active['description'] : 'A curated selection of Pakistan’s most distinctive new addresses: waterfront residences, signature high-rises and private villas, presented by Mark Properties.'">

    <div class="bg-black text-white">
        {{-- ───────────── Hero ───────────── --}}
        {{-- Phones: a fixed height, so iPhone Safari shrinking its address bar while scrolling can't resize the box (and re-crop, "zoom", the photo) --}}
        <section class="relative isolate flex min-h-[38rem] flex-col overflow-hidden sm:min-h-[86svh]">
            <img src="{{ $heroImage }}" alt="" fetchpriority="high"
                class="absolute inset-0 -z-20 h-full w-full md:animate-[luxe-zoom_18s_ease-out_forwards] object-cover">
            <div class="absolute inset-0 -z-10 bg-linear-to-t from-black via-black/30 via-45% to-black/35" aria-hidden="true"></div>
            <div class="absolute inset-0 -z-10 bg-linear-to-r from-black/65 via-black/20 via-50% to-transparent" aria-hidden="true"></div>

            <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col justify-end px-4 pb-10 pt-36 sm:px-6 lg:px-8 lg:pb-14">
                <div class="max-w-3xl">
                    {{-- A collection's own page: the way back sits in the hero, so there's no empty band between the hero and the projects --}}
                    @if ($active)
                        <a href="{{ route('luxe') }}#collection" class="mb-6 inline-flex min-h-11 animate-rise items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white/80 transition-colors hover:border-white hover:text-white">
                            <svg class="h-4 w-4 rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            All collections
                        </a>
                    @endif
                    <h1 class="text-display font-bold leading-[1.05] tracking-tight">
                        @foreach ($headline as $word)
                            <span class="inline-block overflow-hidden pb-1 align-bottom"><span class="inline-block animate-word" style="animation-delay: {{ 150 + $loop->index * 90 }}ms">{{ $word }}</span></span>
                        @endforeach
                    </h1>
                    <p class="text-lead mt-6 max-w-xl animate-rise leading-relaxed text-white/70 [animation-delay:450ms]">
                        {{ $active['description'] ?? 'A curated selection of Pakistan’s most distinctive new addresses: waterfront residences, signature high-rises and private villas.' }}
                    </p>
                    {{-- Two columns on phones (short labels) so both buttons stay on one row --}}
                    <div class="mt-8 grid animate-rise grid-cols-2 gap-3 [animation-delay:600ms] sm:mt-9 sm:flex sm:flex-wrap">
                        <a href="#collection" class="btn btn-light px-3 sm:px-6"><span class="sm:hidden">View collection</span><span class="hidden sm:inline">Explore the collection</span></a>
                        <a href="#advisory" class="btn btn-outline-light px-3 sm:px-6"><span class="sm:hidden">Book consultation</span><span class="hidden sm:inline">Private consultation</span></a>
                    </div>
                </div>

            </div>
        </section>

        {{-- ───────────── Collection tiles ───────────── --}}
        @if (! $active && $collections->isEmpty())
            <section class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8">
                <h2 class="text-section font-bold tracking-tight">New collections are on the way</h2>
                <p class="mx-auto mt-3 max-w-md text-white/60">Our advisors can tell you about upcoming luxury launches before they’re listed.</p>
            </section>
        @endif
        @unless ($active || $collections->isEmpty())
            <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 sm:pt-20 lg:px-8">
                <x-reveal>
                    <h2 class="text-section max-w-2xl text-balance font-bold tracking-tight">Three ways to live exceptionally</h2>
                </x-reveal>
                <div class="mt-8 grid gap-4 sm:mt-12 sm:gap-5 md:grid-cols-3">
                    @foreach ($collections as $collection)
                        <x-reveal :delay="$loop->index * 120">
                            <a href="{{ route('luxe', ['collection' => $collection['slug']]) }}#collection"
                                class="group relative isolate block aspect-[4/3] overflow-hidden rounded-[18px] ring-1 ring-white/10 transition-all duration-700 ease-out-expo hover:-translate-y-1.5 hover:ring-white/30 [@media(hover:hover)]:active:scale-[0.98] md:aspect-[3/4]">
                                <img src="{{ (is_file(public_path('images/luxe/cover-'.$collection['slug'].'.webp')) ? asset('images/luxe/cover-'.$collection['slug'].'.webp') : \App\Support\Projects::fileUrl($collection['cover'], 'images')) }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 -z-10 h-full w-full object-cover transition-transform duration-[1400ms] ease-out-expo group-hover:scale-110">
                                <span class="absolute inset-0 -z-10 bg-linear-to-t from-black/90 via-black/25 via-45% to-transparent to-75%" aria-hidden="true"></span>
                                <span class="absolute inset-x-0 bottom-0 p-5 sm:p-7">
                                    <span class="block h-px w-10 bg-brand transition-all duration-700 ease-out-expo group-hover:w-24"></span>
                                    <span class="mt-4 block text-xl font-bold sm:mt-5 sm:text-2xl">{{ $collection['name'] }}</span>
                                    <span class="mt-2 block text-sm leading-relaxed text-white/65">{{ $collection['tagline'] }}</span>
                                    <span class="mt-4 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink transition-colors duration-300 group-hover:bg-brand group-hover:text-white sm:mt-5">Explore the collection <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                </span>
                            </a>
                        </x-reveal>
                    @endforeach
                </div>
            </section>
        @endunless

        {{-- ───────────── Collection sections ───────────── --}}
        <div id="collection" class="scroll-mt-20">
            @foreach ($shown as $collection)
                {{-- Each collection is its own band (alternating background + borders) so it's clear where one ends and the next starts --}}
                <section @class(['border-y border-white/10 py-12 sm:py-16', 'mt-14 sm:mt-20' => $loop->first && ! $active, 'bg-white/[0.045]' => $loop->odd, 'bg-black' => $loop->even, 'border-t-0' => ! $loop->first])>
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <x-reveal class="flex flex-col gap-4 sm:gap-6 md:flex-row md:items-end md:justify-between">
                            <div class="max-w-2xl">
                                <h2 class="heading-mark text-section font-bold tracking-tight">{{ $collection['name'] }}</h2>
                                {{-- On a collection's own page the hero already shows this description --}}
                                @unless ($active)
                                    <p class="mt-3 leading-relaxed text-white/60">{{ $collection['description'] }}</p>
                                @endunless
                            </div>
                            @unless ($active)
                                <a href="{{ route('luxe', ['collection' => $collection['slug']]) }}#collection" class="group btn btn-outline-light btn-sm shrink-0 gap-2 self-start md:self-auto">View the collection <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                            @endunless
                        </x-reveal>

                        <div class="mt-6 grid gap-4 sm:mt-10 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                            @foreach ($active ? $collection['projects'] : $collection['projects']->take(3) as $project)
                                <x-reveal :delay="($loop->index % 3) * 100">
                                    <x-luxe-card :project="$project" :developer="$developers->get($project['developer'])" />
                                </x-reveal>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        {{-- ───────────── Private advisory ───────────── --}}
        <section id="advisory" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <x-reveal>
                <div class="relative isolate overflow-hidden rounded-[18px] border border-white/10 bg-white/[0.03] p-8 sm:p-12">
                    <div class="grid items-center gap-8 lg:grid-cols-12">
                        <div class="lg:col-span-7">
                            <h2 class="text-section text-balance font-bold tracking-tight">A dedicated advisor for luxury buyers</h2>
                            <p class="mt-4 max-w-xl leading-relaxed text-white/60">Private viewings, early access to new launches and discreet, one-to-one guidance from shortlist to booking.</p>
                        </div>
                        <div class="flex flex-wrap gap-3 lg:col-span-5 lg:justify-end">
                            <a href="{{ route('contact') }}" class="btn btn-primary">Book a consultation</a>
                        </div>
                    </div>
                </div>
            </x-reveal>
        </section>
    </div>
</x-layouts.app>
