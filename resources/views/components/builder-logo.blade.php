{{--
    A builder's logo in grey, crossfading to its original colours when the surrounding link (.group) is hovered.
    Every logo gets the same visual area whatever its shape (App\Support\LogoBox): `size` is the side of the
    equivalent square, and the logo never grows past its parent.
--}}
@props(['developer', 'size' => '3rem'])

@php
    $path = str_contains($developer['logo'], '/') ? $developer['logo'] : 'images/developers/'.$developer['logo'];
    $box = \App\Support\LogoBox::for(preg_replace('/\.webp$/', '-mono.webp', $path));
@endphp

<span {{ $attributes->class('relative block max-h-full max-w-full transition-transform duration-500 ease-out-expo group-hover:scale-105') }}
    style="width: calc({{ $size }} * {{ $box['w'] }}); height: calc({{ $size }} * {{ $box['h'] }})">
    <span role="img" aria-label="{{ $developer['name'] }}"
        style="--logo: url('{{ asset(preg_replace('/\.webp$/', '-mono.webp', $path)) }}')"
        class="absolute inset-0 bg-ink/85 transition-opacity duration-500 [mask:var(--logo)_center/contain_no-repeat] group-hover:opacity-0"></span>
    <img src="{{ asset($path) }}" alt="" width="320" height="320" loading="lazy"
        class="absolute inset-0 h-full w-full object-contain opacity-0 transition-opacity duration-500 group-hover:opacity-100">
</span>
