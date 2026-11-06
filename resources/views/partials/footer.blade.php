@php
    $contact = array_filter(config('site.contact'));

    // Every nav destination, dropdowns flattened; Partner With Us sits after Developers (company links together)
    $links = collect(\App\Support\Nav::items()) // only links that lead to at least one live project
        ->flatMap(fn ($item) => ($item['flatten'] ?? false) ? collect($item['columns'])->flatMap(fn ($column) => $column['links']) : [$item])
        ->values();
    if (($partner = $links->search(fn ($l) => $l['route'] === 'partner')) !== false && ($devs = $links->search(fn ($l) => $l['route'] === 'developers.index')) !== false) {
        $item = $links->pull($partner);
        $links = $links->values();
        $links->splice($links->search(fn ($l) => $l['route'] === 'developers.index') + 1, 0, [$item]);
    }

    $heading = 'mt-3 text-card-title font-bold text-white lg:mt-2.5 lg:text-lg';
@endphp

{{-- Dark footer: brand (+ social icons from 1024px), Explore links, Get in touch. One column on phones (with dividers), three from 1024px. --}}
<footer class="relative mt-auto overflow-hidden bg-ink text-white/65">
    {{-- a faint diagonal sheen, top right --}}
    <div class="pointer-events-none absolute right-0 top-0 h-72 w-72 bg-[linear-gradient(225deg,rgba(255,255,255,0.06),transparent_65%)] sm:h-96 sm:w-[32rem]" aria-hidden="true"></div>

    <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-16 lg:grid-cols-12 lg:gap-12 lg:px-8 lg:py-12">
        {{-- Brand + social --}}
        <div class="lg:col-span-4">
            <div class="border-b border-white/10 pb-8 lg:border-0 lg:pb-0">
                <x-logo variant="light" class="h-16 sm:h-[4.5rem] lg:h-14" />
            </div>
            @if (config('site.tagline'))
                <p class="mt-6 max-w-sm text-sm leading-relaxed">{{ config('site.tagline') }}</p>
            @endif
            @if (config('site.social'))
                @include('partials.footer-social', ['class' => 'mt-6 hidden gap-2.5 lg:flex', 'size' => 'h-10 w-10'])
            @endif
        </div>

        {{-- Explore --}}
        <nav aria-label="Footer" class="lg:col-span-4">
            <span class="block h-1 w-12 rounded-full bg-brand" aria-hidden="true"></span>
            <h2 class="{{ $heading }}">Explore</h2>
            <ul class="mt-5 grid grid-cols-2 gap-x-4 lg:mt-3">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ \App\Support\Nav::href($link) }}" class="group inline-flex min-h-11 items-center gap-1.5 whitespace-nowrap text-lead lg:min-h-9 lg:text-[0.9375rem] text-white/70 transition-colors duration-200 hover:text-white">
                            {{ $link['label'] }}
                            <svg class="h-4 w-4 shrink-0 text-white/40 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Get in touch (forms only: no phone or WhatsApp) --}}
        @if ($contact)
            <div class="border-t border-white/10 pt-10 lg:col-span-4 lg:border-0 lg:pt-0">
                <span class="block h-1 w-12 rounded-full bg-brand" aria-hidden="true"></span>
                <h2 class="{{ $heading }}">Get in touch</h2>
                <ul class="mt-6 space-y-4 text-lead lg:mt-4 lg:space-y-3 lg:text-[0.9375rem]">
                    @isset($contact['email'])
                        <li>
                            <a href="mailto:{{ $contact['email'] }}" class="group flex items-center gap-4 text-white/85 lg:gap-3 transition-colors hover:text-white">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/[0.06] text-brand lg:h-9 lg:w-9 transition-colors duration-300 group-hover:bg-brand group-hover:text-white">
                                    <svg class="h-5 w-5 lg:h-4 lg:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="min-w-0 break-words">{{ $contact['email'] }}</span>
                            </a>
                        </li>
                    @endisset
                    @isset($contact['address'])
                        <li class="flex items-start gap-4 lg:gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/[0.06] text-brand lg:h-9 lg:w-9">
                                <svg class="h-5 w-5 lg:h-4 lg:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                            </span>
                            <address class="pt-2 not-italic leading-relaxed lg:pt-1.5">{{ $contact['address'] }}</address>
                        </li>
                    @endisset
                </ul>

                {{-- Get directions, with the social icons on its right on phones and tablets --}}
                <div class="mt-6 flex flex-wrap items-center justify-between gap-x-4 gap-y-3 lg:mt-3 lg:justify-start">
                    @if ($map = config('site.map'))
                        <a href="{{ $map['link'] }}" target="_blank" rel="noopener"
                            class="group inline-flex min-h-11 items-center gap-3 text-lead font-semibold text-brand lg:text-[0.9375rem] transition-colors hover:text-white">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21 3 3 10.5l7.5 3L13.5 21 21 3Z"/></svg>
                            <span class="underline decoration-2 underline-offset-4">Get directions</span>
                        </a>
                    @endif
                    @if (config('site.social'))
                        @include('partials.footer-social', ['class' => 'flex gap-2 lg:hidden', 'size' => 'h-11 w-11'])
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="relative border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-3 px-4 py-6 text-center text-fine lg:py-4 sm:flex-row sm:justify-between sm:px-6 sm:text-left lg:px-8">
            <p>&copy; {{ date('Y') }} {{ config('site.name') }}. All rights reserved.</p>
            <ul class="flex items-center divide-x divide-white/15">
                <li><a href="{{ route('privacy') }}" class="hit-area px-4 transition-colors hover:text-white sm:ps-0 sm:pe-4">Privacy</a></li>
                <li><a href="{{ route('terms') }}" class="hit-area px-4 transition-colors hover:text-white sm:pe-0">Terms</a></li>
            </ul>
        </div>
    </div>
    <div class="h-1 bg-brand" aria-hidden="true"></div>
</footer>
