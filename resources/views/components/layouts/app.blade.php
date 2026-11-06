{{-- enquireOnPage: the page has its own enquiry form (project pages); the header button opens that instead of /contact --}}
@props(['title' => null, 'description' => null, 'image' => null, 'overlayHeader' => false, 'enquireOnPage' => false, 'headerEnquire' => true, 'share' => null, 'canonical' => null])

@php
    // Long titles (e.g. blog posts) drop the brand suffix so the page name is not cut off in search results
    $fullTitle = $title ? (mb_strlen($title.' | '.config('site.name')) > 60 ? $title : $title.' | '.config('site.name')) : config('site.name');
    $description = $description ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $description)), 157, '…', preserveWords: true) : null;
    // The page's own address without its query string, unless the page names one (e.g. a filtered project list)
    $canonical ??= url()->current();
    // Pages without their own picture share the brand image (L2), so WhatsApp, Facebook and LinkedIn previews are never blank
    $image ??= asset('images/og-default.jpg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#dc1a21">
    {{-- Site icon (browser tabs, bookmarks, Google results, phone home screens), made from the red "mark" tile (L1) --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" href="{{ asset('icon-192.png') }}" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <title>{{ $fullTitle }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Social sharing previews (WhatsApp, Facebook, LinkedIn, X) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('site.name') }}">
    <meta property="og:locale" content="en_PK">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if ($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:image" content="{{ $image }}">
    <meta name="twitter:card" content="summary_large_image">

    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-sm focus:text-white">Skip to content</a>

    @include('partials.header', ['overlay' => $overlayHeader, 'enquireOnPage' => $enquireOnPage, 'headerEnquire' => $headerEnquire, 'share' => $share])

    <main id="main" @class(['flex-1', '-mt-16 sm:-mt-20' => $overlayHeader])>
        {{ $slot }}
    </main>

    @include('partials.footer')
</body>
</html>
