@props(['title', 'lead', 'updated', 'sections', 'description'])

@php
    $contact = config('site.contact');
@endphp

<x-layouts.app :title="$title" :description="$description">
    <x-page-header :title="$title" :lead="$lead">
        <p class="mt-4 text-sm text-muted">Last updated {{ $updated }}</p>
    </x-page-header>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:grid lg:grid-cols-12 lg:gap-14 lg:px-8 lg:py-16">
        <div class="lg:col-span-8">
            {{-- On this page --}}
            <nav aria-label="On this page" class="rounded-[14px] border border-line bg-mist p-6">
                <p class="text-sm font-semibold text-muted">On this page</p>
                {{-- 44px rows on phones and tablets (easy to tap), the compact list from 1024px --}}
                <ol class="mt-3 grid gap-x-10 sm:grid-cols-2 lg:mt-4 lg:gap-y-2.5">
                    @foreach ($sections as $id => $section)
                        <li><a href="#{{ $id }}" class="flex min-h-11 items-center text-[15px] leading-snug text-ink transition-colors hover:text-brand lg:block lg:min-h-0">{{ $section['title'] }}</a></li>
                    @endforeach
                </ol>
            </nav>

            <div class="legal mt-10 max-w-3xl">
                {{ $slot }}
                @foreach ($sections as $id => $section)
                    <section id="{{ $id }}" class="scroll-mt-28">
                        <h2>{{ $section['title'] }}</h2>
                        {!! $section['body'] !!}
                    </section>
                @endforeach
            </div>
        </div>

        {{-- Questions about this policy: a small note, not a lead form (T-02) --}}
        <aside class="mt-14 lg:col-span-4 lg:mt-0">
            <div class="rounded-[14px] border border-line bg-mist p-6 lg:sticky lg:top-28">
                <h2 class="text-base font-bold text-ink">Questions about this page?</h2>
                <p class="mt-2 text-[15px] leading-relaxed text-ink-soft">
                    @if ($contact['email'])
                        Email us at <a href="mailto:{{ $contact['email'] }}" class="font-semibold text-ink underline decoration-line underline-offset-4 hover:decoration-brand">{{ $contact['email'] }}</a> and we’ll reply.
                    @else
                        Send us a message and we’ll reply.
                    @endif
                </p>
                <a href="{{ route('contact', ['topic' => 'other']) }}#message" class="hit-area mt-4 inline-block text-sm font-semibold text-brand hover:text-brand-700">Contact us</a>
            </div>
        </aside>
    </div>
</x-layouts.app>
