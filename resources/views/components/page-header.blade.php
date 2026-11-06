@props(['title', 'lead' => null, 'crumbs' => [], 'breadcrumb' => null])

{{--
    The standard page title band (famproperties-style): faint grid, breadcrumb, H1, lead.
    $crumbs: ['Label' => url, ...] — the page itself is added last automatically.
    Default slot: extra content under the lead. Named slots: "aside" (right-hand column, e.g. a search box), "media" (e.g. a logo left of the title), "above" (e.g. a badge above the title).
--}}
<header {{ $attributes->class('relative isolate border-b border-line') }}>
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(var(--color-line)_1px,transparent_1px),linear-gradient(90deg,var(--color-line)_1px,transparent_1px)] bg-[size:64px_64px] opacity-50 [mask-image:linear-gradient(to_bottom,black,transparent)]" aria-hidden="true"></div>
    <div class="mx-auto max-w-7xl px-4 pb-12 pt-10 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-sm text-muted">
                <li><a href="{{ route('home') }}" class="transition-colors hover:text-ink">Home</a></li>
                @foreach ($crumbs as $label => $url)
                    <li aria-hidden="true"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></li>
                    <li><a href="{{ $url }}" class="transition-colors hover:text-ink">{{ $label }}</a></li>
                @endforeach
                <li aria-hidden="true"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></li>
                <li class="text-ink" aria-current="page">{{ $breadcrumb ?? $title }}</li>
            </ol>
        </nav>

        <div @class(['mt-6', 'lg:flex lg:items-end lg:justify-between lg:gap-12' => isset($aside)])>
            <div @class(['max-w-3xl', 'flex flex-col gap-6 sm:flex-row sm:items-center' => isset($media)])>
                {{ $media ?? '' }}
                <div>
                @isset($above){{ $above }}@endisset
                <h1 class="text-page-title font-medium leading-[1.1] text-ink">{{ $title }}</h1>
                @if ($lead)
                    <p class="mt-4 max-w-2xl text-lead leading-relaxed text-ink-soft">{{ $lead }}</p>
                @endif
                {{ $slot }}
                </div>
            </div>
            @isset($aside)
                <div class="mt-8 shrink-0 lg:mt-0 lg:w-96">{{ $aside }}</div>
            @endisset
        </div>
    </div>
</header>
