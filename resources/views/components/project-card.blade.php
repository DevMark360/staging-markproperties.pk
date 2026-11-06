
{{--
    The one project card used across the site (listing, developer pages, home, similar projects).
    The whole card is a single link; the builder's logo sits in the footer row. No prices.
--}}
{{-- compact: on phones a wider, shorter image and an arrow instead of the full-width button (long one-column lists) --}}
@props(['project', 'developer' => null, 'rank' => null, 'showDeveloper' => true, 'compact' => false])

@php
    $status = \App\Support\Projects::stageName($project['status']);
    $city = \App\Support\Projects::cityName($project['city']);
@endphp

<article {{ $attributes->class('group relative flex flex-col overflow-hidden rounded-[14px] border border-line bg-white transition-[border-color,box-shadow] duration-300 hover:border-ink/15 hover:shadow-[0_18px_40px_-24px_rgba(20,20,20,.4)]') }}>
    <div @class(['relative overflow-hidden bg-mist', 'aspect-[16/9] sm:aspect-[4/3]' => $compact, 'aspect-[4/3]' => ! $compact])>
        @if ($project['image'])
            <img src="{{ \App\Support\Projects::imageUrl($project['image']) }}" alt="" width="800" height="600" loading="lazy"
                class="h-full w-full object-cover transition-transform duration-[1200ms] ease-out-expo group-hover:scale-[1.04]">
        @endif

        @if ($status)
            <span class="absolute left-3.5 top-3.5 inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-ink shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-brand" aria-hidden="true"></span>{{ $status }}
            </span>
        @endif

        @if ($rank)
            <span class="absolute right-3.5 top-3.5 rounded-full bg-brand px-2.5 py-1 text-xs font-semibold text-white">#{{ $rank }}</span>
        @endif
    </div>

    <div @class(['flex flex-1 flex-col', 'p-4 pe-14 sm:p-5' => $compact, 'p-5' => ! $compact])>
        {{-- Two lines reserved from tablet up, so every card in a row has the same shape and the buttons line up without a gap --}}
        <h3 class="line-clamp-2 text-lg font-semibold leading-snug text-ink sm:min-h-[2lh]">
            {{-- Stretched link: the whole card is clickable --}}
            <a href="{{ route('projects.show', $project['slug']) }}" class="transition-colors after:absolute after:inset-0 group-hover:text-brand">{{ $project['name'] }}</a>
        </h3>
        @php $place = $project['area'] === $city ? $city : $project['area'].', '.$city; @endphp
        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-ink-soft" title="{{ $place }}">
            <svg class="h-4 w-4 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
            <span class="min-w-0 truncate">{{ $place }}</span>
        </p>

        @if ($showDeveloper && $developer)
            <div class="mt-3 flex items-center gap-2.5">
                    <span class="grid h-7 w-7 shrink-0 place-items-center overflow-hidden rounded-md bg-white ring-1 ring-line">
                        @if ($developer['logo'])
                            <img src="{{ \App\Support\Projects::fileUrl($developer['logo'], 'images/developers') }}" alt="" width="320" height="320" loading="lazy" class="h-full w-full object-contain p-0.5">
                        @else
                            <span class="text-xs font-bold text-ink">{{ str($developer['name'])->substr(0, 1) }}</span>
                        @endif
                    </span>
                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-ink">{{ $developer['name'] }}</span>
            </div>
        @endif

        {{-- Looks like a button; the stretched title link above makes the whole card clickable --}}
        <div @class(['mt-auto pt-4', 'hidden sm:block' => $compact])>
            <span class="flex w-full items-center justify-center gap-2 rounded-lg bg-ink px-4 py-2.5 text-sm font-semibold text-white transition-colors duration-300 group-hover:bg-brand" aria-hidden="true">
                View project
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </div>
    </div>

    @if ($compact)
        <span class="absolute bottom-4 right-4 grid h-9 w-9 place-items-center rounded-full bg-ink text-white transition-colors duration-300 group-hover:bg-brand sm:hidden" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
    @endif
</article>
