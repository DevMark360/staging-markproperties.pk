
{{-- Image card for the Luxe Collection (tall from 640px, landscape on phones to keep the page short); details sit on the photo, no prices --}}
@props(['project', 'developer' => null])

@php
    $status = \App\Support\Projects::stageName($project['status']);
    $city = \App\Support\Projects::cityName($project['city']);
@endphp

<a href="{{ route('projects.show', $project['slug']) }}"
    {{ $attributes->class('group relative isolate block aspect-[4/3] overflow-hidden sm:aspect-[4/5] rounded-[14px] bg-white/5 ring-1 ring-white/10 transition-all duration-700 ease-out-expo hover:-translate-y-1.5 hover:shadow-[0_40px_70px_-30px_rgba(0,0,0,.9)] hover:ring-white/25 [@media(hover:hover)]:active:scale-[0.98]') }}>
    @if ($project['photo'])
        <img src="{{ $project['photo'] }}" alt="{{ $project['name'] }}" loading="lazy" decoding="async"
            class="absolute inset-0 -z-10 h-full w-full object-cover transition-transform duration-[1400ms] ease-out-expo group-hover:scale-110">
    @endif
    {{-- Dark only behind the text at the bottom (and a light top band for the badges); the photo itself stays clear --}}
    <span class="absolute inset-0 -z-10 bg-linear-to-t from-black/90 via-black/20 via-45% to-black/10 to-80%" aria-hidden="true"></span>
    <span class="absolute inset-x-0 bottom-0 h-1 origin-left scale-x-0 bg-brand transition-transform duration-700 ease-out-expo group-hover:scale-x-100" aria-hidden="true"></span>

    <span class="absolute left-4 right-4 top-4 flex items-start justify-between gap-3 sm:left-5 sm:right-5 sm:top-5">
        @if ($status)
            <span class="rounded-full border border-white/25 bg-black/55 px-3 py-1 text-xs font-semibold text-white">{{ $status }}</span>
        @endif
        @if ($developer['logo'] ?? null)
            <span class="ms-auto grid h-10 w-10 shrink-0 sm:h-12 sm:w-12 place-items-center overflow-hidden rounded-xl bg-white p-1 shadow-lg transition-transform duration-500 group-hover:scale-110" title="{{ $developer['name'] }}">
                <img src="{{ \App\Support\Projects::fileUrl($developer['logo'], 'images/developers') }}" alt="{{ $developer['name'] }} logo" loading="lazy" class="h-full w-full object-contain">
            </span>
        @endif
    </span>

    <span class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
        @if ($developer)
            <span class="block text-sm text-white/60">{{ $developer['name'] }}</span>
        @endif
        <span class="mt-1 block text-xl font-bold leading-tight text-white sm:mt-2 sm:text-2xl">{{ $project['name'] }}</span>
        <span class="mt-2 block text-sm text-white/70">{{ $project['area'] === $city ? $city : $project['area'].', '.$city }}</span>
        {{-- Shown on hover with a mouse; always shown on touch screens, which have no hover --}}
        <span class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-500 ease-out-expo group-hover:grid-rows-[1fr] [@media(hover:none)]:grid-rows-[1fr]">
            <span class="overflow-hidden">
                <span class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-white">View project <span class="grid h-7 w-7 place-items-center rounded-full bg-brand"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></span></span>
            </span>
        </span>
    </span>
</a>
