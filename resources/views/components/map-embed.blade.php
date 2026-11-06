{{--
    A Google Maps embed that is never an empty box while it loads: a light map-style placeholder (pin + address)
    shows until the frame reports "load", then the map fades in over it. (A page can't tell a failed map from a
    working one inside Google's frame, so on failure the browser's own message shows in the frame.)
    The slot is laid over the map (e.g. a "Get directions" button). Size the box with the class attribute.
--}}
@props(['src', 'title', 'address' => null])

<div x-data="{ loaded: false }" {{ $attributes->class('group relative isolate overflow-hidden bg-mist ring-1 ring-line') }}>
    {{-- Placeholder --}}
    <div class="absolute inset-0 grid place-items-center p-6 text-center transition-opacity duration-500 [background-image:linear-gradient(var(--color-line)_1px,transparent_1px),linear-gradient(90deg,var(--color-line)_1px,transparent_1px)] [background-size:44px_44px]"
        :class="loaded && 'opacity-0'" aria-hidden="true">
        <div class="max-w-xs">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-white text-brand shadow-[0_10px_24px_-10px_rgba(20,20,20,.35)]">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
            </span>
            @if ($address)
                <p class="mt-3 line-clamp-2 text-sm font-semibold text-ink">{{ $address }}</p>
            @endif
            <p class="mt-1 text-xs text-muted">Loading map…</p>
        </div>
    </div>

    <iframe src="{{ $src }}" title="{{ $title }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" @load="loaded = true"
        class="absolute inset-0 h-full w-full border-0 opacity-0 grayscale-[.35] transition-[opacity,filter] duration-700 group-hover:grayscale-0"
        :class="loaded && 'opacity-100'"></iframe>

    {{ $slot }}
</div>
