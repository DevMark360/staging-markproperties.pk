@props(['variant' => 'dark'])

{{--
    dark  = charcoal text, for light backgrounds
    light = white text, for dark backgrounds
    auto  = white over the hero, charcoal once the header turns solid
--}}
<a href="{{ route('home') }}" {{ $attributes->class('group inline-flex shrink-0 items-center') }} aria-label="{{ config('site.name') }}, home">
    <span class="relative block h-full transition-transform duration-500 ease-out-expo group-hover:scale-[1.03]">
        @if ($variant === 'auto')
            <img src="{{ asset('images/logo-dark.png') }}" alt="{{ config('site.name') }} and AbadRaho.com" width="720" height="226"
                class="h-full w-auto opacity-0 transition-opacity duration-500 solid:opacity-100">
            <img src="{{ asset('images/logo-light.png') }}" alt="" width="720" height="226"
                class="absolute inset-0 h-full w-auto transition-opacity duration-500 solid:opacity-0">
        @else
            <img src="{{ asset('images/logo-'.$variant.'.png') }}" alt="{{ config('site.name') }} and AbadRaho.com" width="720" height="226" class="h-full w-auto">
        @endif
    </span>
</a>
