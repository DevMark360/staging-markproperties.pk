@props(['code', 'title', 'message'])

{{-- Branded error page inside the normal site layout (header and footer) --}}
@push('head')
    <meta name="robots" content="noindex">
@endpush

<x-layouts.app :title="$title">
    <section class="relative isolate border-b border-line">
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(var(--color-line)_1px,transparent_1px),linear-gradient(90deg,var(--color-line)_1px,transparent_1px)] bg-[size:64px_64px] opacity-50 [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
        <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
            <p class="text-sm font-semibold text-muted">Error {{ $code }}</p>
            <h1 class="text-page-title mt-3 max-w-2xl text-ink font-medium">{{ $title }}</h1>
            <p class="text-lead mt-5 max-w-xl leading-relaxed text-ink-soft">{{ $message }}</p>
            <div class="mt-9 flex flex-wrap gap-3">
                {{ $slot }}
            </div>
        </div>
    </section>
</x-layouts.app>
