@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => 'Mark Properties Blog',
        'url' => route('blog'),
        'blogPost' => $posts->map(fn ($post) => [
            '@type' => 'BlogPosting',
            'headline' => $post['title'],
            'url' => route('blog.show', $post['slug']),
            'datePublished' => $post['date']->toDateString(),
            'image' => $post['image'],
        ])->all(),
    ];
    $pill = 'rounded-full border px-4 py-2 text-[15px] font-medium transition-colors duration-200';
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app title="Pakistan real estate blog" :canonical="$canonical"
    description="Guides and tips from Mark Properties on buying off-plan property in Pakistan: what to check, mistakes to avoid and how to plan your investment.">

    <x-page-header title="Pakistan real estate blog" breadcrumb="Blog"
        lead="Guides for buying property in Pakistan: what to check before you commit, the mistakes to avoid and how to plan your investment. Written by the Mark Properties team." />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-14">
        {{-- Categories: only those with at least one post (Blog::categories), so no filter ever leads to an empty page --}}
        <nav aria-label="Blog categories" class="flex flex-wrap gap-2.5">
            <a href="{{ route('blog') }}" @class([$pill, 'border-ink bg-ink text-white' => ! $category, 'border-line text-ink-soft hover:border-ink/40 hover:text-ink' => $category]) @if (! $category) aria-current="page" @endif>All</a>
            @foreach ($categories as $slug => $label)
                <a href="{{ route('blog', ['category' => $slug]) }}" @class([$pill, 'border-ink bg-ink text-white' => $category === $slug, 'border-line text-ink-soft hover:border-ink/40 hover:text-ink' => $category !== $slug]) @if ($category === $slug) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <x-cta-band title="Looking for the right project?"
        lead="Tell us what you’re looking for and an advisor will shortlist new launches that fit your budget and timeline."
        secondary="Browse projects" :secondary-href="route('projects.index')" />
</x-layouts.app>
