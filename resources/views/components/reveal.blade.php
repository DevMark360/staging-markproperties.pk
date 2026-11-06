@props(['delay' => 0])

{{-- Fades content up when it scrolls into view. Stays visible if JavaScript is unavailable. --}}
<div x-data="{ shown: false }" x-intersect.once.margin.-60px="shown = true"
    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
    style="transition-delay: {{ (int) $delay }}ms"
    {{ $attributes->class('transition duration-1000 ease-out-expo') }}>
    {{ $slot }}
</div>
