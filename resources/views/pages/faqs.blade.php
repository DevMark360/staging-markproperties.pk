@php
    use Illuminate\Support\Str;

    $faqs = collect(config('faqs'))->map(fn ($faq) => $faq + ['id' => Str::slug($faq['question'])]);
    $contact = config('site.contact');
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
        ])->all(),
    ];
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app title="Off-plan property questions, answered"
    description="Answers to common questions about buying off-plan property in Pakistan: project stages, approvals, documents, taxes, overseas buyers and site visits.">

    <x-page-header title="Off-plan property questions, answered" breadcrumb="FAQs"
        lead="The questions our advisors are asked most about buying off-plan in Pakistan. Each answer has its own link, so you can send someone the answer rather than the search." />

    {{-- Questions --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-14"
        x-data="{ open: null, copied: null, copyOk: true,
            init() { const id = location.hash.slice(1), el = id && document.getElementById(id); if (el) { this.open = id; this.$nextTick(() => el.scrollIntoView({ block: 'start' })); } },
            toggle(id) { this.open = this.open === id ? null : id; if (this.open) history.replaceState(null, '', '#' + id); },
            async copy(id) { this.copyOk = await copyText(location.origin + location.pathname + '#' + id); this.copied = id; setTimeout(() => this.copied = null, 2000); } }">
        {{-- Same row as the home FAQ: only the question in the heading (F-01), a + that turns into x when open (F-02) --}}
        <div class="max-w-4xl space-y-3">
            @foreach ($faqs as $faq)
                <div id="{{ $faq['id'] }}" class="scroll-mt-28 overflow-hidden rounded-2xl border bg-white transition-all duration-500 ease-out-expo"
                    :class="open === @js($faq['id']) ? 'border-transparent shadow-[0_24px_48px_-28px_rgba(20,20,20,.35)]' : 'border-line hover:border-ink/25'">
                    <h2>
                        <button type="button" @click="toggle(@js($faq['id']))" :aria-expanded="open === @js($faq['id'])" aria-controls="{{ $faq['id'] }}-answer"
                            class="group flex w-full items-center justify-between gap-5 px-5 py-4 text-left sm:px-7 sm:py-5">
                            <span class="text-base font-bold leading-snug transition-colors duration-300 sm:text-[17px]"
                                :class="open === @js($faq['id']) ? 'text-brand' : 'text-ink group-hover:text-brand'">{{ $faq['question'] }}</span>
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full transition-all duration-500 ease-out-expo"
                                :class="open === @js($faq['id']) ? 'rotate-45 bg-brand text-white' : 'bg-mist text-ink group-hover:bg-ink group-hover:text-white'" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12" stroke-linecap="round"/></svg>
                            </span>
                        </button>
                    </h2>
                    <div id="{{ $faq['id'] }}-answer" role="region" aria-labelledby="{{ $faq['id'] }}" x-show="open === @js($faq['id'])" x-collapse x-cloak>
                        <div class="px-5 pb-6 sm:px-7">
                            <p class="text-[15px] leading-relaxed text-ink-soft sm:text-base">{{ $faq['answer'] }}</p>
                            <button type="button" @click="copy(@js($faq['id']))"
                                class="mt-4 inline-flex h-10 items-center gap-2 rounded-lg border border-line px-3.5 text-sm font-semibold text-ink transition-colors hover:border-ink">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>
                                <span x-text="copied === @js($faq['id']) ? (copyOk ? 'Link copied' : 'Couldn’t copy') : 'Copy link'" aria-live="polite">Copy link</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <x-cta-band title="Didn’t find your answer?"
        lead="Ask an advisor directly. We’ll help you with approvals, documents and choosing the right project." />
</x-layouts.app>
