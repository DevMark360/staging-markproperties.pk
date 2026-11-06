{{-- Blog post card (blog page, related articles, home). heading: h2 on the blog page, h3 inside a titled section --}}
@props(['post', 'heading' => 'h2'])

@php($href = route('blog.show', $post['slug']))

<article {{ $attributes->class('group flex flex-col overflow-hidden rounded-[14px] border border-line bg-white transition-[border-color,box-shadow] duration-300 hover:border-ink/15 hover:shadow-[0_18px_40px_-24px_rgba(20,20,20,.35)]') }}>
    <a href="{{ $href }}" tabindex="-1" aria-hidden="true" class="block aspect-[16/9] overflow-hidden bg-mist">
        <img src="{{ $post['image'] }}" alt="" width="800" height="450" loading="lazy"
            class="h-full w-full object-cover transition-transform duration-[1200ms] ease-out-expo group-hover:scale-[1.04]">
    </a>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <{{ $heading }} class="text-lg font-semibold leading-snug text-ink">
            <a href="{{ $href }}" class="transition-colors group-hover:text-brand">{{ $post['title'] }}</a>
        </{{ $heading }}>
        <p class="mt-3 line-clamp-3 flex-1 text-[15px] leading-relaxed text-muted">{{ $post['excerpt'] }}</p>

        <div class="mt-5 flex items-center gap-3 border-t border-line pt-4">
            <x-blog-author />
            <div class="text-[13px] leading-tight">
                <p class="font-medium text-ink">Mark Properties</p>
                <p class="mt-0.5 text-muted"><time datetime="{{ $post['date']->toDateString() }}">{{ $post['date']->format('j M Y') }}</time> · {{ $post['minutes'] }} min read</p>
            </div>
        </div>
    </div>
</article>
