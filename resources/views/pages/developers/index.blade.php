<x-layouts.app title="Trusted Developers in Pakistan"
    description="Explore the builders behind Pakistan's newest off-plan launches, including Saima Group, Kings Group, Falaknaz Group and Roomi Builders, with Mark Properties.">

    <div x-data="{ q: '' }">
        <x-page-header title="Trusted developers across Pakistan" breadcrumb="Developers"
            lead="The builders behind the newest off-plan launches we market, from established groups to new names on the skyline.">
            <x-slot:aside>
                <label class="relative block">
                    <span class="sr-only">Search developers</span>
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                    <input type="search" x-model="q" placeholder="Search developers"
                        class="w-full rounded-xl border border-line bg-white py-3.5 pe-4 ps-11 text-[15px] text-ink placeholder:text-muted hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5">
                </label>
            </x-slot:aside>
        </x-page-header>

        @foreach (['Partner developers' => [$partners, true], 'More developers' => [$others, false]] as $heading => [$list, $large])
            @if ($list->isNotEmpty())
                <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8 {{ $loop->last ? 'pb-20' : '' }}"
                    x-show="! q || @js($list->pluck('name')->map(fn ($n) => mb_strtolower($n))).some(n => n.includes(q.toLowerCase()))">
                    <x-reveal>
                        <h2 class="text-section font-bold tracking-tight text-ink">{{ $heading }}</h2>
                    </x-reveal>
                    <ul @class(['mt-8 grid gap-4', 'grid-cols-2 lg:grid-cols-4' => $large, 'grid-cols-2 md:grid-cols-3 lg:grid-cols-5' => ! $large])>
                        @foreach ($list as $developer)
                            <li x-show="! q || @js(mb_strtolower($developer['name'])).includes(q.toLowerCase())"
                                x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                                <x-reveal :delay="($loop->index % 5) * 60" class="h-full">
                                    <x-developer-card :developer="$developer" :large="$large" />
                                </x-reveal>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endforeach

        {{-- No developers listed yet --}}
        @if ($partners->isEmpty() && $others->isEmpty())
            <section class="mx-auto max-w-7xl px-4 pb-20 pt-14 text-center sm:px-6 lg:px-8">
                <p class="text-lg font-semibold text-ink">Developers will appear here as their projects are listed.</p>
                <a href="{{ route('partner') }}" class="btn btn-outline mt-5">Are you a builder? Partner with us</a>
            </section>
        @endif

        {{-- No match --}}
        <section x-cloak x-show="q && ! @js($partners->concat($others)->pluck('name')->map(fn ($n) => mb_strtolower($n))).some(n => n.includes(q.toLowerCase()))"
            class="mx-auto max-w-7xl px-4 pb-20 pt-14 sm:px-6 lg:px-8">
            <div class="rounded-[18px] border border-dashed border-line px-6 py-14 text-center">
                <h2 class="text-xl font-bold text-ink">No developer matches “<span x-text="q"></span>”</h2>
                <p class="mt-2 text-sm text-muted">Try another name, or browse all projects.</p>
                <a href="{{ route('projects.index') }}" class="btn btn-dark btn-sm mt-6">Browse projects</a>
            </div>
        </section>

    </div>

    <x-cta-band title="Launching a new project?"
        lead="Partner with Mark Properties to market your launch to serious buyers and investors across Pakistan."
        primary="Partner with us" :primary-href="route('partner')" />
</x-layouts.app>
