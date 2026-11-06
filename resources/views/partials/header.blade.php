@use('App\Support\Nav')

@php
    $nav = Nav::items(); // only links that lead to at least one live project
    $overlay ??= false;
    $projects = \App\Support\Projects::all()->keyBy('slug');
    $activeIndex = collect($nav)->search(fn ($item) => Nav::isActive($item)); // first match only
@endphp

{{--
    On overlay pages the header sits transparent over the hero and turns solid white on scroll
    or while a dropdown is open. Children style their solid state with the `solid:` variant (app.css).
--}}
<header
    x-data="{
        scrolled: false,
        open: false,
        menu: null,
        closeTimer: null,
        overlay: @js($overlay),
        get solid() { return ! this.overlay || this.scrolled || this.menu !== null },
        show(k) { clearTimeout(this.closeTimer); this.menu = k },
        hide() { this.closeTimer = setTimeout(() => this.menu = null, 140) },
    }"
    x-init="scrolled = window.scrollY > 24"
    @scroll.window.passive="scrolled = window.scrollY > 24"
    @keydown.escape.window="open = false; menu = null"
    x-effect="document.documentElement.classList.toggle('overflow-hidden', open)"
    data-solid="{{ $overlay ? 'false' : 'true' }}"
    :data-solid="solid ? 'true' : 'false'"
    class="sticky top-0 z-50 border-b border-transparent transition-[background-color,border-color,box-shadow] duration-500 solid:border-line solid:bg-white/95 solid:shadow-[0_10px_30px_-18px_rgba(0,0,0,.35)]"
>
    {{-- Fixed height: the bar used to shrink on scroll, which moved the whole page by 16px mid-scroll (felt like a stutter).
         No backdrop blur either: the bar is near-opaque, and a live blur is redrawn on every scroll frame on phones. --}}
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:h-20 sm:px-6 lg:px-8">
        <x-logo :variant="$overlay ? 'auto' : 'dark'" class="h-11 sm:h-14" />

        {{-- Desktop navigation --}}
        <nav aria-label="Primary" class="hidden xl:block" @click.outside="menu = null">
            <ul class="flex items-center gap-7">
                @foreach ($nav as $k => $item)
                    @php
                        $active = $k === $activeIndex;
                        $tone = $active ? 'text-white solid:text-ink' : 'text-white/80 hover:text-white solid:text-ink-soft solid:hover:text-ink';
                    @endphp

                    @if (! empty($item['columns']))
                        {{-- Not `relative`: the panel spans the full header width --}}
                        <li @mouseenter="show({{ $k }})" @mouseleave="hide()">
                            <button type="button" @click="menu = menu === {{ $k }} ? null : {{ $k }}" :aria-expanded="menu === {{ $k }}"
                                class="nav-link flex items-center gap-1.5 py-2 text-sm font-semibold transition-colors duration-300 {{ $tone }}"
                                :class="menu === {{ $k }} && 'text-ink!'">
                                {{ $item['label'] }}
                                <svg class="h-3.5 w-3.5 transition-transform duration-300" :class="menu === {{ $k }} && 'rotate-180'" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>

                            <div x-cloak x-show="menu === {{ $k }}"
                                x-transition:enter="transition ease-out-expo duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                class="absolute inset-x-0 top-full border-t border-line bg-white shadow-[0_40px_60px_-30px_rgba(0,0,0,.35)]">
                                <div class="mx-auto grid max-w-7xl grid-cols-12 gap-10 px-8 py-10">
                                    @php($stacked = $item['stacked'] ?? false)
                                    <div @class([
                                        'col-span-8 grid',
                                        'gap-10 grid-cols-2' => ! $stacked && count($item['columns']) > 1,
                                        'gap-10 grid-cols-1 max-w-xs' => ! $stacked && count($item['columns']) === 1,
                                        'gap-7' => $stacked, // groups one above the other, links in 3 columns
                                    ])>
                                        @foreach ($item['columns'] as $c => $column)
                                            <div>
                                                <p class="text-sm font-semibold text-muted">{{ $column['heading'] }}</p>
                                                <ul @class(['mt-4 gap-x-6', 'grid grid-cols-3' => $stacked, 'grid grid-cols-2' => ! $stacked && count($column['links']) > 5])>
                                                    @foreach ($column['links'] as $l => $link)
                                                        <li class="transition duration-500 ease-out-expo"
                                                            :class="menu === {{ $k }} ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-2'"
                                                            style="transition-delay: {{ 60 + ($c * 4 + $l) * 30 }}ms">
                                                            <a href="{{ Nav::href($link) }}" @if (url()->full() === Nav::href($link)) aria-current="page" @endif class="group -mx-3 flex items-center justify-between rounded-xl px-3 py-2.5 text-[15px] font-semibold text-ink transition-colors duration-200 hover:bg-mist hover:text-brand">
                                                                {{ $link['label'] }}
                                                                <svg class="h-4 w-4 -translate-x-1 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="col-span-4">
                                        @if (isset($item['feature']) && ($feature = $projects->get($item['feature'])))
                                            <a href="{{ route('projects.show', $feature['slug']) }}" class="group relative block aspect-[4/3] overflow-hidden rounded-2xl bg-ink">
                                                <img src="{{ \App\Support\Projects::imageUrl($feature['image']) }}" alt="" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 ease-out-expo group-hover:scale-105">
                                                <span class="absolute inset-0 bg-linear-to-t from-ink via-ink/30 to-transparent"></span>
                                                <span class="absolute inset-x-0 bottom-0 p-5 text-white">
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-2.5 py-0.5 text-xs font-semibold text-ink">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>Featured
                                                    </span>
                                                    <span class="mt-2 block text-lg font-bold leading-tight">{{ $feature['name'] }}</span>
                                                    <span class="mt-0.5 flex items-center justify-between text-sm text-white/70">
                                                        {{ $feature['area'] }}, {{ \App\Support\Projects::cityName($feature['city']) }}
                                                        <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    </span>
                                                </span>
                                            </a>
                                        @else
                                            <div class="relative h-full overflow-hidden rounded-2xl bg-ink p-6 text-white">
                                                <span class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand/30 blur-3xl" aria-hidden="true"></span>
                                                <p class="relative text-xl font-bold leading-snug">Talk to a Mark Properties advisor</p>
                                                <p class="relative mt-2 text-sm text-white/60">Project details, site visits and booking support across Pakistan.</p>
                                                <div class="relative mt-6 flex flex-wrap gap-2">
                                                    <a href="{{ route('contact', ['topic' => 'buying']) }}#message" class="btn btn-primary btn-sm">Send an enquiry</a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @else
                        <li>
                            <a href="{{ Nav::href($item) }}" @if ($active) aria-current="page" @endif
                                class="nav-link block py-2 text-sm font-semibold transition-colors duration-300 {{ $tone }}">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        {{-- Actions --}}
        <div class="flex items-center gap-2.5">

            {{-- headerEnquire: false on pages that show their own Enquire button (e.g. a developer page) --}}
            @if ($headerEnquire ?? true)
                @if ($enquireOnPage ?? false)
                    {{-- Pages with their own enquiry form: phones and tablets use the page's sticky bar, so this shows from
                         1024px and opens that form instead of /contact (one enquiry button per screen) --}}
                    <button type="button" @click="$dispatch('enquire', { interest: 'general' })" class="hit-area btn btn-primary btn-sm hidden lg:inline-flex">Enquire now</button>
                @else
                    {{-- Shown on phones too (shorter label there), so the main lead action is never hidden behind the menu --}}
                    <a href="{{ route('contact') }}" class="hit-area btn btn-primary btn-sm h-10 px-3.5 sm:h-auto sm:px-4">
                        <span class="sm:hidden">Enquire</span><span class="hidden sm:inline">Enquire now</span>
                    </a>
                @endif
            @endif

            {{-- share: a page title (e.g. a project) adds a Share icon: the phone's share sheet, or copies the link --}}
            @if ($share ?? null)
                <div class="relative" x-data="{ copied: false }">
                    <button type="button" aria-label="Share this page" title="Share"
                        @click="navigator.share ? navigator.share({ title: @js($share), url: location.href }).catch(() => {}) : navigator.clipboard.writeText(location.href).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="hit-area grid h-10 w-10 place-items-center rounded-full border border-white/25 text-white transition-all duration-300 hover:border-white active:scale-95 solid:border-line solid:text-ink solid:hover:border-ink">
                        <svg x-show="! copied" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 15V3m0 0L8 7m4-4 4 4M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg x-cloak x-show="copied" class="h-[18px] w-[18px] text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <span x-cloak x-show="copied" x-transition.opacity role="status"
                        class="pointer-events-none absolute right-0 top-full mt-2 whitespace-nowrap rounded-full bg-ink px-3 py-1.5 text-xs font-semibold text-white shadow-lg">Link copied</span>
                </div>
            @endif

            <button type="button" @click="open = true" :aria-expanded="open" aria-controls="mobile-menu" aria-label="Open menu"
                class="hit-area grid h-10 w-10 place-items-center rounded-full border border-white/25 text-white transition-all duration-300 hover:border-white active:scale-95 solid:border-line solid:text-ink solid:hover:border-ink xl:hidden">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M10 17h10" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu (teleported so the header's backdrop blur doesn't trap it) --}}
    <template x-teleport="body">
        <div x-cloak x-show="open" x-trap.inert="open" id="mobile-menu" class="fixed inset-0 z-[60] xl:hidden" role="dialog" aria-modal="true" aria-label="Menu">
            <div x-show="open" @click="open = false"
                x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-ink/50 backdrop-blur-sm"></div>

            {{-- Only as tall as its contents (not the whole screen): drops down from the top; the dimmed page shows below --}}
            <div x-show="open"
                x-transition:enter="transition duration-400 ease-out-expo" x-transition:enter-start="-translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-4 opacity-0"
                class="absolute right-0 top-0 flex max-h-[100svh] w-full max-w-sm flex-col overflow-hidden rounded-b-3xl bg-white shadow-2xl sm:rounded-bl-3xl sm:rounded-br-none">
                <div class="flex h-20 items-center justify-between border-b border-line px-5">
                    <x-logo class="h-11" />
                    <button type="button" @click="open = false" aria-label="Close menu"
                        class="hit-area grid h-10 w-10 place-items-center rounded-full border border-line text-ink transition-all duration-300 hover:rotate-90 hover:border-ink">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <nav aria-label="Mobile" class="min-h-0 overflow-y-auto px-5 pb-2 pt-4">
                    <ul class="space-y-0.5">
                        @foreach ($nav as $k => $item)
                            @php($active = $k === $activeIndex)
                            <li class="transition-all duration-500 ease-out-expo"
                                :class="open ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-6'"
                                :style="{ transitionDelay: open ? '{{ 120 + $loop->index * 45 }}ms' : '0ms' }">
                                @if (! empty($item['columns']))
                                    <div x-data="{ sub: {{ $active ? 'true' : 'false' }} }">
                                        <button type="button" @click="sub = ! sub" :aria-expanded="sub"
                                            class="flex min-h-12 w-full items-center justify-between rounded-xl px-2 text-left text-[17px] transition-colors hover:bg-mist {{ $active ? 'font-semibold text-brand' : 'font-medium text-ink' }}">
                                            {{ $item['label'] }}
                                            <svg class="h-5 w-5 text-ink transition-transform duration-300" :class="sub && 'rotate-180'" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                        <div x-show="sub" x-collapse>
                                            <div class="mb-3 ms-3 space-y-4 border-s-2 border-line ps-3 pt-1">
                                                @foreach ($item['columns'] as $column)
                                                    <div>
                                                        <p class="px-3 text-sm font-semibold text-muted">{{ $column['heading'] }}</p>
                                                        <ul class="mt-1">
                                                            @foreach ($column['links'] as $link)
                                                                <li>
                                                                    <a href="{{ Nav::href($link) }}" @if (url()->full() === Nav::href($link)) aria-current="page" @endif class="flex min-h-11 items-center rounded-lg px-3 py-2 text-[15px] font-semibold text-ink-soft transition-colors hover:bg-mist hover:text-ink">{{ $link['label'] }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ Nav::href($item) }}" @if ($active) aria-current="page" @endif
                                        class="flex min-h-12 items-center rounded-xl px-2 text-[17px] transition-colors hover:bg-mist {{ $active ? 'font-semibold text-brand' : 'font-medium text-ink' }}">
                                        {{ $item['label'] }}
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    {{-- Get in touch: email and social links (no phone or WhatsApp support) --}}
                    @php($email = config('site.contact.email'))
                    @if ($email || config('site.social'))
                        <div class="mt-6 border-t border-line px-2 pt-6">
                            <p class="text-[15px] font-bold text-ink">Get in touch</p>
                            @if ($email)
                                <a href="mailto:{{ $email }}" class="mt-1 flex min-h-11 items-center gap-2.5 text-[15px] font-medium text-ink transition-colors hover:text-brand">
                                    <svg class="h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ $email }}
                                </a>
                            @endif
                            @if (config('site.social'))
                                @include('partials.footer-social', ['class' => 'mt-3 flex gap-2.5', 'size' => 'h-11 w-11', 'tone' => 'light'])
                            @endif
                        </div>
                    @endif
                </nav>

                <div class="p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
                    @if ($enquireOnPage ?? false)
                        <button type="button" @click="open = false; $dispatch('enquire', { interest: 'general' })" class="btn btn-primary h-12 w-full rounded-xl text-base">Enquire now</button>
                    @else
                        <a href="{{ route('contact') }}" class="btn btn-primary h-12 w-full rounded-xl text-base">Enquire now</a>
                    @endif
                </div>
            </div>
        </div>
    </template>
</header>
