@props(['size' => 'h-9 w-9'])

{{-- Byline avatar: every article is published by the Mark Properties team (the red "mark" block of the logo, without AbadRaho) --}}
<img src="{{ asset('images/mark-avatar.webp') }}" alt="" width="256" height="256" loading="lazy"
    {{ $attributes->class([$size, 'shrink-0 rounded-full object-cover']) }}>
