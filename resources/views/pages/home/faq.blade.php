{{-- ───────────── FAQ ───────────── --}}
@if ($faqs)
    @php
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ], $faqs),
        ];
    @endphp
    <section class="py-section bg-mist">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-28">
                    <x-reveal>
                        <x-section-header size="lg" title="Buying off-plan property in Pakistan"
                            lead="Answers to the questions buyers ask us most, from approvals and documents to site visits." />
                    </x-reveal>

                    {{-- Laptop and up: beside the questions. Phones and tablets get it after the list --}}
                    <x-reveal :delay="120" class="mt-10 hidden lg:block">
                        <x-faq-help-card />
                    </x-reveal>
                </div>
            </div>

            <div class="lg:col-span-7" x-data="{ active: 0 }">
                <ul class="space-y-3">
                    @foreach ($faqs as $faq)
                        <li>
                            <x-reveal :delay="min($loop->index, 5) * 60">
                                <div class="overflow-hidden rounded-2xl border bg-white transition-all duration-500 ease-out-expo"
                                    :class="active === {{ $loop->index }} ? 'border-transparent shadow-[0_24px_48px_-28px_rgba(20,20,20,.35)]' : 'border-line hover:border-ink/25'">
                                    <h3>
                                        <button type="button" id="faq-q-{{ $loop->index }}" aria-controls="faq-a-{{ $loop->index }}"
                                            @click="active = active === {{ $loop->index }} ? null : {{ $loop->index }}"
                                            :aria-expanded="active === {{ $loop->index }}"
                                            class="group flex w-full items-center justify-between gap-6 px-6 py-5 text-left sm:px-7">
                                            <span class="text-base font-bold leading-snug transition-colors duration-300 sm:text-[17px]"
                                                :class="active === {{ $loop->index }} ? 'text-brand' : 'text-ink group-hover:text-brand'">{{ $faq['question'] }}</span>
                                            <span class="relative grid h-9 w-9 shrink-0 place-items-center rounded-full transition-all duration-500 ease-out-expo"
                                                :class="active === {{ $loop->index }} ? 'rotate-45 bg-brand text-white' : 'bg-mist text-ink group-hover:bg-ink group-hover:text-white'" aria-hidden="true">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12" stroke-linecap="round"/></svg>
                                            </span>
                                        </button>
                                    </h3>
                                    <div id="faq-a-{{ $loop->index }}" role="region" aria-labelledby="faq-q-{{ $loop->index }}"
                                        x-show="active === {{ $loop->index }}" x-collapse @if (! $loop->first) x-cloak @endif>
                                        <p class="px-6 pb-6 text-[15px] leading-relaxed text-muted sm:px-7">{{ $faq['answer'] }}</p>
                                    </div>
                                </div>
                            </x-reveal>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-6 text-fine leading-relaxed text-muted">General information only, not legal, tax or financial advice. Confirm details with the developer and a qualified professional before you pay.</p>
                <x-faq-help-card class="mt-8 lg:hidden" />
            </div>
        </div>
    </section>

    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endif
