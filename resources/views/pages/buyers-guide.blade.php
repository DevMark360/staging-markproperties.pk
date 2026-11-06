@php
    $chevron = '<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $summary = 'Buying off-plan property in Pakistan means booking a unit directly with a builder before construction is complete. Before you pay anything, verify the builder, the land title and the project’s approvals, visit the site, and read the booking form and agreement, not just the brochure, because the agreement sets out the possession date, what happens if construction is delayed, and your rights to cancel or transfer. Keep every receipt and letter, track construction, inspect the unit at possession and complete the registration of your title.';

    $sections = [
        'how-it-works' => 'How buying off-plan works',
        'step-by-step' => 'Step by step',
        'documents' => 'Documents to prepare',
        'costs' => 'Costs to plan for',
        'overseas' => 'Buying from overseas',
        'mistakes' => 'Common mistakes to avoid',
        'questions' => 'Common questions',
    ];

    $steps = [
        ['Decide what you are buying for', 'Live in it, rent it out or hold it? The answer changes which city, area, property type and possession date make sense. Set your budget and timeline before you start looking.'],
        ['Shortlist projects and builders', 'Compare locations, project stages and the builder’s record of delivered projects. Where you can, visit buildings the builder has already completed.'],
        ['Verify approvals and land title', 'Ask for the approved building plan and, in Sindh, the SBCA No Objection Certificate (NOC) for sale and advertisement. In other cities, check approval from the relevant authority (LDA in Lahore, CDA in Islamabad or RDA in Rawalpindi) and confirm the builder’s title to the land.'],
        ['Visit the site and the sales office', 'See the location, access roads and surroundings, and the progress on site. Ask the sales team the questions the brochure does not answer.'],
        ['Read the booking form and agreement', 'Check the possession date, the clauses that cover delays and cancellation, refund terms, whether you can transfer the unit before possession, the specifications and the maintenance arrangements. Ask for anything unclear in writing.'],
        ['Book and keep records', 'Pay through the bank in the builder’s company name, never in cash to an individual. Keep every receipt, your booking confirmation and, later, your allotment letter.'],
        ['Track construction and take possession', 'Follow progress on site, inspect the unit carefully before accepting possession, and complete the registration of your title documents with the relevant authority.'],
    ];

    $documents = [
        'CNIC, or NICOP for overseas Pakistanis',
        'Recent photographs',
        'NTN or proof of tax filer status',
        'Completed and signed booking form',
        'Bank receipts for every payment',
        'Booking confirmation and allotment letter',
        'Signed agreement with the builder',
        'Possession letter and registered title documents',
    ];

    $costs = [
        ['Federal taxes', 'Advance tax on property purchases is collected by the FBR. The rate depends on your filer status and the property value.'],
        ['Provincial charges', 'Stamp duty and registration or transfer fees apply when your title is registered, and vary by province and city.'],
        ['Maintenance and utilities', 'Expect maintenance charges once the building is handed over, and charges for utility connections.'],
        ['Fit-out and furnishing', 'Budget for anything the specifications do not include, such as kitchens, fixtures, furniture and appliances.'],
    ];

    $mistakes = [
        'Choosing from renders without verifying the approvals.',
        'Paying in cash or to a personal account.',
        'Relying on the brochure instead of the agreement.',
        'Ignoring the clauses that cover delays and cancellation.',
        'Not budgeting for taxes, registration and fit-out.',
        'Accepting possession without inspecting the unit.',
    ];

    $faqs = [
        ['What is off-plan property?', 'A unit you book directly with a builder before construction is complete, sometimes before it even begins. You take possession once the building is finished and handed over.'],
        ['What should I check first?', 'The builder’s track record, their title to the land, and the project’s approvals. In Karachi, that means the approved building plan and the SBCA NOC for sale and advertisement.'],
        ['Which documents matter most?', 'Your booking form and agreement, every bank receipt, your allotment letter and, at the end, your possession letter and registered title documents.'],
        ['What costs apply apart from the unit itself?', 'Federal advance tax, provincial stamp duty and registration or transfer fees, maintenance charges and fit-out. Rates change from time to time, so confirm current figures before you pay.'],
        ['How long does it take?', 'It depends on the project stage: pre-launch projects take longest, while under-construction projects are further along. The agreement should state the possession date.'],
        ['Can overseas Pakistanis buy off-plan?', 'Yes. You can book remotely with your NICOP, and many buyers invest through a Roshan Digital Account. A trusted representative can act for you through a power of attorney.'],
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Article',
                'headline' => 'Buying off-plan property in Pakistan: complete guide',
                'description' => Str::limit($summary, 300),
                'author' => ['@type' => 'Organization', 'name' => 'Mark Properties'],
                'publisher' => ['@type' => 'Organization', 'name' => 'Mark Properties', 'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo-dark.png')]],
                'mainEntityOfPage' => route('guide'),
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => collect($faqs)->map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]])->all(),
            ],
        ],
    ];
    $h2 = 'text-section scroll-mt-28 font-bold tracking-tight text-ink';
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app title="Buying off-plan property in Pakistan: complete guide"
    description="How to buy off-plan property in Pakistan: verify the builder and approvals, read the agreement, the documents you need, the costs to plan for and the mistakes to avoid.">

    {{-- Two lines in the header; the full summary opens the guide below (G-01) --}}
    <x-page-header title="Buying off-plan property in Pakistan: complete guide" breadcrumb="Buyer’s guide"
        lead="Booking a home before it’s built? What to check, what to sign and what to plan for, step by step." />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:grid lg:grid-cols-12 lg:gap-16 lg:px-8 lg:py-16">
        {{-- Sticky guide nav --}}
        <aside class="mb-12 lg:col-span-4 lg:mb-0 xl:col-span-3">
            {{-- Current section = the last one above 30% of the screen (scroll position, so it's right after a quick jump).
                 Phones/tablets: a collapsed box (tap to open), so the guide starts right away; desktop: always open and sticky. --}}
            <nav aria-label="In this guide" class="rounded-2xl bg-ink p-5 text-white lg:sticky lg:top-28"
                x-data="{ ...scrollSpy('[data-guide-section]', @js(array_key_first($sections))), open: false }">
                {{-- Phones/tablets: a toggle button; from 1024px the list is always open, so a plain heading takes its place (T5) --}}
                <p class="hidden px-2 text-sm font-semibold text-white/50 lg:block">In this guide</p>
                <button type="button" @click="open = ! open" :aria-expanded="open" aria-controls="guide-contents"
                    class="flex w-full items-center justify-between px-2 text-left text-sm font-semibold text-white/70 lg:hidden">
                    In this guide
                    <svg class="h-4 w-4 transition-transform duration-300 lg:hidden" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div id="guide-contents" class="hidden lg:block" :class="open && '!block'">
                <ol class="mt-3 space-y-1">
                    @foreach ($sections as $id => $title)
                        <li>
                            <a href="#{{ $id }}" @click="current = @js($id); open = false" class="flex items-center gap-3 rounded-xl px-2 py-2 text-[15px] leading-snug transition-colors duration-200"
                                :class="current === @js($id) ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:text-white'">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg transition-colors duration-200" :class="current === @js($id) ? 'bg-brand' : 'bg-white/10'">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                                </span>
                                {{ $title }}
                            </a>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-4 space-y-1 border-t border-white/10 px-2 pt-4 text-[15px]">
                    <a href="{{ route('faqs') }}" class="flex items-center justify-between py-1.5 font-semibold text-white hover:text-brand">All FAQs {!! $chevron !!}</a>
                    <a href="{{ route('blog') }}" class="flex items-center justify-between py-1.5 font-semibold text-white hover:text-brand">Blog {!! $chevron !!}</a>
                </div>
                </div>
            </nav>
        </aside>

        {{-- Guide --}}
        <article class="max-w-3xl text-[clamp(1rem,0.93rem+0.35vw,1.0625rem)] leading-[1.75] text-ink-soft lg:col-span-8 xl:col-span-9">
            <div class="mb-12 rounded-2xl border border-line bg-mist p-5 sm:p-7">
                <p class="text-sm font-semibold text-ink">The short version</p>
                <p class="mt-2 text-[15px] leading-relaxed text-ink-soft sm:text-base">{{ $summary }}</p>
            </div>

            <section id="how-it-works" data-guide-section class="scroll-mt-28">
                <h2 class="{{ $h2 }}">How buying off-plan in Pakistan works</h2>
                <p class="mt-4">You book a unit directly with the builder, usually by submitting a booking form and making the first payment. The builder issues a booking confirmation and, later, an allotment letter for your unit. Construction continues while you make the payments set out in your agreement, and once the building is complete the builder hands over possession.</p>
                <p class="mt-4">After possession, your ownership is registered with the relevant authority. For apartments in Karachi, this is typically through a registered sub-lease. The agreement you sign with the builder governs every step, so it matters more than any brochure or advertisement.</p>
                <p class="mt-4">Mark Properties is a marketing and sales partner, not the builder. We help you shortlist projects, arrange site visits and understand each development; your booking and agreement are always made directly with the builder.</p>
            </section>

            <section id="step-by-step" data-guide-section class="mt-14 scroll-mt-28">
                <h2 class="{{ $h2 }}">Step by step</h2>
                <ol class="mt-6 space-y-8">
                    @foreach ($steps as $i => [$title, $text])
                        <li>
                            <h3 class="text-xl font-semibold text-ink">{{ $i + 1 }}. {{ $title }}</h3>
                            <p class="mt-2">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section id="documents" data-guide-section class="mt-14 scroll-mt-28">
                <h2 class="{{ $h2 }}">Documents to prepare</h2>
                <p class="mt-4">Keep a folder, on paper and scanned, with everything from your first enquiry to registration.</p>
                <ul class="mt-6 grid gap-x-8 gap-y-3 sm:grid-cols-2">
                    @foreach ($documents as $doc)
                        <li class="flex gap-3 text-[16px]">
                            <svg class="mt-1.5 h-4 w-4 shrink-0 text-brand" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m4.5 10.5 3.5 3.5 7.5-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $doc }}
                        </li>
                    @endforeach
                </ul>
            </section>

            <section id="costs" data-guide-section class="mt-14 scroll-mt-28">
                <h2 class="{{ $h2 }}">Costs to plan for</h2>
                <p class="mt-4">Beyond the unit itself, budget for these. Rates are set by the federal and provincial governments and change from time to time, so confirm current figures before you pay.</p>
                <div class="mt-6 overflow-hidden rounded-[14px] border border-line">
                    @foreach ($costs as [$title, $text])
                        <div class="grid gap-1 border-line px-5 py-4 sm:grid-cols-[13rem_1fr] sm:gap-6 [&:not(:last-child)]:border-b">
                            <p class="font-semibold text-ink">{{ $title }}</p>
                            <p class="text-[16px] leading-relaxed">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="overseas" data-guide-section class="mt-14 scroll-mt-28">
                <h2 class="{{ $h2 }}">Buying from overseas</h2>
                <p class="mt-4">Overseas Pakistanis can book off-plan projects remotely using their NICOP, and many invest through a Roshan Digital Account. If you cannot be in Pakistan for possession or registration, a trusted representative can act for you through a power of attorney attested at a Pakistani mission abroad.</p>
                <p class="mt-4">Ask for a virtual walkthrough of the site and recent construction photos, and keep copies of every document by email.</p>
            </section>

            <section id="mistakes" data-guide-section class="mt-14 scroll-mt-28">
                <h2 class="{{ $h2 }}">Common mistakes to avoid</h2>
                <ul class="mt-5 list-disc space-y-2 pl-5 marker:text-brand">
                    @foreach ($mistakes as $mistake)
                        <li>{{ $mistake }}</li>
                    @endforeach
                </ul>
                <p class="mt-5">Off-plan property carries construction, delivery and contract risk. Take advice where the agreement or your obligations are unclear.</p>
                <p class="mt-6 text-[16px] italic text-muted">This guide provides general information, not legal, tax or financial advice. Requirements change and depend on the project, the city and your circumstances. Confirm current details with the relevant authority and qualified advisers before acting.</p>
                <div class="btn-pair mt-8 border-y border-line py-8">
                    <a href="{{ route('projects.index') }}" class="btn btn-primary">Explore projects</a>
                    <a href="{{ route('contact', ['topic' => 'buying']) }}#message" class="btn btn-outline">Talk to an advisor</a>
                </div>
            </section>

            <section id="questions" data-guide-section class="mt-14 scroll-mt-28" x-data="{ open: 0 }">
                <h2 class="text-section font-bold tracking-tight text-ink">Common questions</h2>
                <div class="mt-6 border-t border-line">
                    @foreach ($faqs as $i => [$q, $a])
                        <div class="border-b border-line">
                            <h3>
                                <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}"
                                    class="flex w-full items-center justify-between gap-6 py-5 text-left text-[17px] font-semibold transition-colors"
                                    :class="open === {{ $i }} ? 'text-brand' : 'text-ink hover:text-brand'">
                                    {{ $q }}
                                    <svg class="h-5 w-5 shrink-0 text-muted transition-transform duration-300 ease-out-expo" :class="open === {{ $i }} && 'rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                            </h3>
                            <div x-show="open === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif>
                                <p class="pb-6 text-[16px] leading-relaxed">{{ $a }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </article>
    </div>

    <x-cta-band title="Want help with your shortlist?"
        lead="Tell us what you’re looking for and an advisor will help you compare projects, check approvals and arrange site visits."
        secondary="Explore projects" :secondary-href="route('projects.index')" />
</x-layouts.app>
