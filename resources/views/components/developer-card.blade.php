{{-- Developer card: monochrome logo that crossfades to its original colours on hover; no counts --}}
@props(['developer', 'large' => false])

<a href="{{ route('developers.show', $developer['slug']) }}"
    {{ $attributes->class([
        'group relative flex h-full flex-col overflow-hidden rounded-[14px] border border-line bg-white transition-[border-color,box-shadow,translate] duration-300 hover:-translate-y-1 hover:border-ink/15 hover:shadow-[0_18px_40px_-24px_rgba(20,20,20,.35)] motion-reduce:hover:translate-y-0',
    ]) }}>

    <span @class(['grid place-items-center bg-mist transition-colors duration-500 group-hover:bg-white', 'h-40' => $large, 'h-32' => ! $large])>
        @if ($developer['logo'])
            <x-builder-logo :developer="$developer" :size="$large ? 'clamp(3.6rem, 3rem + 1.6vw, 4.6rem)' : 'clamp(3rem, 2.6rem + 1.2vw, 3.8rem)'" />
        @else
            <span class="text-3xl font-extrabold text-ink">{{ str($developer['name'])->substr(0, 1) }}</span>
        @endif
    </span>

    <span class="flex flex-1 flex-col gap-1 border-t border-line p-5">
        <span @class(['font-bold leading-snug text-ink transition-colors duration-300 group-hover:text-brand', 'text-lg' => $large, 'text-base' => ! $large])>{{ $developer['name'] }}</span>
        @if (! empty($developer['cities']))
            <span class="text-sm text-muted">{{ implode(', ', $developer['cities']) }}</span>
        @endif
        {{-- The whole card is the link; this row just shows it --}}
        <span class="mt-auto flex items-center justify-between gap-3 pt-4 text-sm font-semibold text-ink">
            View projects
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-mist text-ink transition-colors duration-300 group-hover:bg-brand group-hover:text-white" aria-hidden="true">
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </span>
    </span>
</a>
