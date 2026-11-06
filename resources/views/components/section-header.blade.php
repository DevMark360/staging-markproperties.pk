@props(['title', 'lead' => null, 'dark' => false, 'size' => null, 'action' => null, 'href' => null, 'onMist' => false])

{{-- Section heading: plain title (no label above it), optional lead and a text-link action. size="lg" for the home page. --}}
<div {{ $attributes->class('flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="max-w-3xl">
        <h2 @class(['font-bold leading-tight tracking-tight', 'text-section-lg' => $size === 'lg', 'text-section' => $size !== 'lg', 'text-white' => $dark, 'text-ink' => ! $dark])>{{ $title }}</h2>
        @if ($lead)
            <p @class(['mt-2 max-w-2xl text-lead leading-relaxed sm:mt-3', 'text-white/65' => $dark, 'text-ink-soft' => ! $dark])>{{ $lead }}</p>
        @endif
    </div>
    @if ($action && $href)
        {{-- Pill link with an arrow circle that turns red and rotates on hover --}}
        <a href="{{ $href }}" @class([
            'group inline-flex shrink-0 items-center gap-4 self-start rounded-full py-2 pe-2 ps-6 text-xs font-bold uppercase tracking-[0.14em] transition-all duration-300 sm:self-auto',
            'bg-white/10 text-white hover:bg-white/15' => $dark,
            'bg-mist text-ink hover:bg-line' => ! $dark && ! $onMist,
            // On a light-grey (mist) section the pill is white, or it would disappear into the background
            'bg-white text-ink hover:bg-line' => ! $dark && $onMist,
        ])>
            {{ $action }}
            <span @class([
                'grid h-10 w-10 place-items-center rounded-full transition-all duration-500 ease-out-expo group-hover:rotate-45 group-hover:bg-brand group-hover:text-white',
                'bg-white text-ink' => $dark,
                'bg-ink text-white' => ! $dark,
            ])>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 14 14 6M7 6h7v7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </a>
    @endif
</div>
