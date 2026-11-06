@props([
    'title',
    'lead',
    'primary' => 'Talk to an advisor',
    'primaryHref' => null,
    'secondary' => null,
    'secondaryHref' => null,
    'secondaryExternal' => false,
])

{{-- The one closing call-to-action used at the bottom of inner pages --}}
<section {{ $attributes->class('bg-ink text-white') }}>
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-16 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8 lg:py-20">
        <div class="max-w-2xl">
            <h2 class="text-section-lg font-bold leading-tight tracking-tight">{{ $title }}</h2>
            <p class="mt-3 leading-relaxed text-white/65">{{ $lead }}</p>
        </div>
        {{-- Phones: the two buttons side by side in equal columns (a long label may wrap inside its button) --}}
        <div @class(['gap-3', 'grid grid-cols-2 sm:flex sm:flex-wrap' => $secondary, 'flex' => ! $secondary])>
            <a href="{{ $primaryHref ?? route('contact', ['topic' => 'buying']).'#message' }}" class="btn btn-primary whitespace-normal px-2 text-center text-sm sm:whitespace-nowrap sm:px-6 sm:text-[15px]">{{ $primary }}</a>
            @if ($secondary)
                <a href="{{ $secondaryHref }}" class="btn btn-outline-light whitespace-normal px-2 text-center text-sm sm:whitespace-nowrap sm:px-6 sm:text-[15px]" @if ($secondaryExternal) target="_blank" rel="noopener" @endif>{{ $secondary }}</a>
            @endif
        </div>
    </div>
</section>
