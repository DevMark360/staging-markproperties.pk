@props(['name' => '', 'icon' => null])

{{-- An amenity/utility icon: the one chosen in the admin, or a guess from the name; see App\Support\FeatureIcons --}}
@php
    $key = $icon ?? \App\Support\FeatureIcons::guess($name);
    $path = (\App\Support\FeatureIcons::ICONS[$key] ?? \App\Support\FeatureIcons::ICONS[\App\Support\FeatureIcons::FALLBACK])[2];
@endphp

<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'aria-hidden' => 'true']) }}>{!! $path !!}</svg>
