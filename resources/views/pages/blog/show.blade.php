@php
    $url = route('blog.show', $post['slug']);
    $image = $post['image_large'];
    $toc = $post['toc'];
    // Share: LinkedIn only, next to "Copy link" (user's choice)
    $share = [
        'LinkedIn' => ['https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($url), 'M7 10v7M7 7v.01M11 17v-7m0 3a3 3 0 0 1 6 0v4'],
    ];
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BlogPosting',
                'headline' => $post['title'],
                'description' => $post['excerpt'],
                'image' => $image,
                'datePublished' => $post['date']->toDateString(),
                'mainEntityOfPage' => $url,
                'author' => ['@type' => 'Organization', 'name' => 'Mark Properties'],
                'publisher' => ['@type' => 'Organization', 'name' => 'Mark Properties', 'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo-dark.png')]],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $post['category'], 'item' => route('blog', ['category' => $post['category_slug']])],
                    ['@type' => 'ListItem', 'position' => 4, 'name' => $post['title'], 'item' => $url],
                ],
            ],
        ],
    ];
    $chevron = '<svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

@push('head')
    <meta property="article:published_time" content="{{ $post['date']->toIso8601String() }}">
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app :title="$post['title']" :description="$post['excerpt']" :image="$image">

    <article>
        {{-- Header band --}}
        <header class="relative isolate border-b border-line">
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(var(--color-line)_1px,transparent_1px),linear-gradient(90deg,var(--color-line)_1px,transparent_1px)] bg-[size:64px_64px] opacity-50 [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
            <div class="mx-auto max-w-7xl px-4 pb-10 pt-10 sm:px-6 lg:px-8">
                <nav aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-2 text-sm text-muted">
                        <li><a href="{{ route('home') }}" class="transition-colors hover:text-ink">Home</a></li>
                        <li aria-hidden="true">{!! $chevron !!}</li>
                        <li><a href="{{ route('blog') }}" class="transition-colors hover:text-ink">Blog</a></li>
                        <li aria-hidden="true">{!! $chevron !!}</li>
                        <li><a href="{{ route('blog', ['category' => $post['category_slug']]) }}" class="transition-colors hover:text-ink">{{ $post['category'] }}</a></li>
                        <li aria-hidden="true" class="hidden sm:block">{!! $chevron !!}</li>
                        <li class="hidden text-ink sm:block" aria-current="page">{{ $post['title'] }}</li>
                    </ol>
                </nav>

                {{-- The category is already in the breadcrumb, so no separate label above the title --}}
                <h1 class="text-page-title mt-6 max-w-4xl leading-[1.12] text-ink font-medium">{{ $post['title'] }}</h1>
                <p class="text-lead mt-5 max-w-3xl leading-relaxed text-ink-soft">{{ $post['excerpt'] }}</p>

                {{-- Byline: author | date | read time, one row with dividers ("min read" shortens to "min" on phones so it fits) --}}
                <div class="mt-7 flex flex-wrap items-center gap-x-2 gap-y-2 whitespace-nowrap text-[13px] text-ink-soft sm:gap-x-4 sm:text-[15px]">
                    <span class="flex items-center gap-1.5 sm:gap-3">
                        <x-blog-author size="h-7 w-7 sm:h-10 sm:w-10" />
                        <span class="font-semibold text-ink">Mark Properties</span>
                    </span>
                    <span class="h-4 w-px bg-line sm:h-5" aria-hidden="true"></span>
                    <span class="flex items-center gap-1 sm:gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-muted sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4" stroke-linecap="round"/></svg>
                        <time datetime="{{ $post['date']->toDateString() }}">{{ $post['date']->format('j M Y') }}</time>
                    </span>
                    <span class="h-4 w-px bg-line sm:h-5" aria-hidden="true"></span>
                    <span class="flex items-center gap-1 sm:gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-muted sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ $post['minutes'] }} min<span class="hidden sm:inline"> read</span></span>
                    </span>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
            <img src="{{ $image }}" alt="" width="1600" height="700"
                class="aspect-[16/10] w-full rounded-2xl bg-mist object-cover sm:aspect-[16/7]">
        </div>

        {{-- Body with contents --}}
        <div @class(['mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16', 'lg:grid lg:grid-cols-12 lg:gap-12' => count($toc) > 1])>
            @if (count($toc) > 1)
                <aside class="mb-10 lg:col-span-3 lg:mb-0">
                    <nav aria-label="In this article" class="rounded-2xl bg-ink p-6 text-white lg:sticky lg:top-28"
                        {{-- Current section = the last heading above 30% of the screen, worked out from the scroll position, so it's right even after a quick jump --}}
                        x-data="scrollSpy('.article h2[id]', @js(array_key_first($toc)))">
                        <p class="text-sm font-semibold text-white/50">In this article</p>
                        <ol class="mt-4 space-y-1 border-l border-white/15">
                            @foreach ($toc as $id => $heading)
                                <li>
                                    <a href="#{{ $id }}" @click="current = @js($id)"
                                        class="-ml-px flex min-h-11 items-center border-l-2 py-1.5 pl-4 text-[15px] leading-snug transition-colors duration-200 lg:block lg:min-h-0"
                                        :class="current === @js($id) ? 'border-brand font-semibold text-white' : 'border-transparent text-white/65 hover:text-white'">{{ $heading }}</a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </aside>
            @endif

            {{-- Lines capped at about 68 characters (40rem at 18px Inter): easier to read than full width (was about 95) --}}
            <div @class(['article max-w-[40rem]', 'lg:col-span-9' => count($toc) > 1])>
                {!! $post['body'] !!}
            </div>
        </div>
    </article>

    {{-- Share --}}
    <div class="border-t border-line">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{ copied: false }">
            {{-- One row on every screen: the question, then Copy link and LinkedIn beside it --}}
            <div class="flex max-w-3xl items-center gap-2 rounded-2xl border border-line bg-mist px-4 py-4 sm:gap-3 sm:px-5">
                <p class="me-auto text-sm font-semibold text-ink sm:me-1 sm:text-[15px]">Found this useful?</p>
                <button type="button" @click="copied = (await copyText(@js($url))) ? 'yes' : 'no'; setTimeout(() => copied = false, 2000)"
                    class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg border border-line bg-white px-3 text-sm font-semibold text-ink transition-colors hover:border-ink sm:px-4">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>
                    <span x-text="{ yes: 'Link copied', no: 'Couldn’t copy' }[copied] ?? 'Copy link'" aria-live="polite">Copy link</span>
                </button>
                @foreach ($share as $network => [$link, $icon])
                    <a href="{{ $link }}" target="_blank" rel="noopener" aria-label="Share on {{ $network }}"
                        class="hit-area grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-line bg-white text-ink-soft transition-colors hover:border-ink hover:text-ink">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- More articles --}}
    @if ($more->isNotEmpty())
        <section class="border-t border-line bg-mist">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                <h2 class="text-section font-bold tracking-tight text-ink">More articles</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($more as $item)
                        <x-post-card :post="$item" heading="h3" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band title="Thinking about buying?"
        lead="Tell us what you’re looking for and an advisor will come back to you with projects that fit."
        secondary="Read more articles" :secondary-href="route('blog')" />
</x-layouts.app>
