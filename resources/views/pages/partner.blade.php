@php
    $contact = config('site.contact');
    $sent = (bool) session('partner_sent');
    $field = 'mt-2 block w-full rounded-xl border border-line bg-white px-4 py-3 text-[15px] text-ink transition duration-200 placeholder:text-muted/60 hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5';
    $label = 'text-sm font-semibold text-ink';
    $chevron = '<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $check = '<svg class="mt-1 h-4 w-4 shrink-0 text-brand" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m4.5 10.5 3.5 3.5 7.5-8" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    // At a glance: [short title, note, icon path] (one short line each, so the cells stay even on phones)
    $glance = [
        ['For builders', 'Residential, commercial and mixed-use projects', 'M4 21V8l6-4v17M10 21V10h10v11M4 21h17M7 9h.01M7 13h.01M7 17h.01M14 14h2M14 17h2'],
        ['Off-plan projects', 'Pre-launch and under construction', 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['Across Pakistan', 'Based in Karachi', 'M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Zm0-9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
        ['Easy to start', 'Share the project and its approvals', 'M7 3h7l5 5v13H7V3Zm7 0v5h5M10 13h6M10 17h6'],
    ];

    $steps = [
        ['We call you', 'A partnerships advisor gets in touch to learn about your project and what you need from a sales partner.'],
        ['We review the project', 'We look at the location, the stage of construction and the approvals, so we can present it with confidence.'],
        ['You decide', 'We agree a plan together: how the project is presented and the terms. Nothing is signed until you are happy.'],
    ];

    $questions = [
        [
            'id' => 'how-do-you-market-a-project',
            'q' => 'How does Mark Properties market a project?',
            'short' => 'We present your project on Mark Properties and AbadRaho.com, promote it to buyers looking for new launches, and our advisors follow up every enquiry through to a site visit and booking.',
            'steps' => [
                ['Project page', 'A dedicated page with the gallery, amenities, location and highlights of your project.'],
                ['Promotion', 'Featured placement on Mark Properties and AbadRaho.com, campaigns on our social channels and launch alerts to buyers waiting for new projects.'],
                ['Enquiries', 'Our advisors answer every enquiry, qualify the buyer and keep in touch until they are ready.'],
                ['Site visits', 'We arrange and accompany visits to the site and your sales office.'],
                ['Booking', 'We support the buyer through booking with your sales team and share buyer feedback with you.'],
            ],
        ],
        [
            'id' => 'which-projects-do-you-take-on',
            'q' => 'Which projects do you take on?',
            'short' => 'Off-plan residential, commercial and mixed-use projects that are pre-launch or under construction, in Karachi and in cities across Pakistan.',
            'body' => 'We look for projects we can recommend to buyers with confidence: a clear title to the land, the required approvals, and a builder who stands behind the possession timeline.',
        ],
        [
            'id' => 'what-do-you-need-to-get-started',
            'q' => 'What do you need from us to get started?',
            'short' => 'The basic details of the project and its approvals. We ask for the rest during our first conversation.',
            'list' => [
                'Approved building plan from the relevant authority (SBCA, LDA, CDA or RDA)',
                'NOC for sale and advertisement, where required',
                'Land ownership documents',
                'Brochure, renders and location details',
                'Amenities and expected possession timeline',
                'Your booking process and point of contact',
            ],
        ],
        [
            'id' => 'do-buyers-book-with-you',
            'q' => 'Do buyers book with Mark Properties or with us?',
            'short' => 'With you. Bookings and agreements are made directly with the builder. We guide the buyer and support your sales team through the process.',
        ],
        [
            'id' => 'how-are-terms-agreed',
            'q' => 'How are partnership terms agreed?',
            'short' => 'Project by project. Once we have reviewed your project and understood your goals, we propose terms and agree them with you before any work starts.',
        ],
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($questions)->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['short']],
        ])->all(),
    ];
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app title="Partner with Mark Properties as a builder"
    description="Builders: market and sell your off-plan project with Mark Properties: project pages, promotion on AbadRaho.com, enquiry follow-up and site visits."
    :image="asset('images/hero/partner-with-us.webp')" :overlay-header="true">

    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-ink text-white">
        {{-- Towers sit on the right of the photo: phones crop towards them, and the overlay is dark only behind the text --}}
        <img src="{{ asset('images/hero/partner-with-us.webp') }}" alt="" width="1672" height="941" fetchpriority="high" class="absolute inset-0 -z-20 h-full w-full object-cover object-[75%_center] sm:object-center">
        {{-- Phones and tablets: the text spans the width, so an even overlay; from 1024px a gradient that leaves the towers bright --}}
        <div class="absolute inset-0 -z-10 bg-ink/65 lg:bg-transparent lg:bg-[linear-gradient(90deg,rgba(20,20,20,.88)_0%,rgba(20,20,20,.62)_45%,rgba(20,20,20,.15)_100%)]"></div>
        {{-- The header sits over the photo (see-through until you scroll, like Home), so the top padding includes its height --}}
        <div class="mx-auto max-w-7xl px-4 pb-20 pt-28 sm:px-6 sm:pt-32 lg:px-8 lg:pb-28 lg:pt-36">
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-sm text-white/60">
                    <li><a href="{{ route('home') }}" class="transition-colors hover:text-white">Home</a></li>
                    <li aria-hidden="true">{!! $chevron !!}</li>
                    <li class="text-white" aria-current="page">Partner with us</li>
                </ol>
            </nav>
            <h1 class="text-display mt-10 max-w-3xl font-bold leading-[1.08] tracking-tight">Sell your project with Mark Properties</h1>
            <p class="text-lead mt-6 max-w-2xl leading-relaxed text-white/75">We present your off-plan project to buyers searching for new launches across Pakistan, and our advisors take every enquiry through to a site visit and a booking with your team.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="#partner-form" class="btn btn-primary">Tell us about your project</a>
            </div>
        </div>
    </section>

    {{-- At a glance: icon + short title + note; 2 x 2 on phones and tablets, one row from 1024px --}}
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <h2 class="sr-only">Partnering with us at a glance</h2>
        <dl class="grid grid-cols-2 overflow-hidden rounded-[14px] border border-line bg-white lg:grid-cols-4">
            @foreach ($glance as [$title, $note, $icon])
                <div class="flex flex-col gap-3 border-line p-4 sm:flex-row sm:items-start sm:gap-4 sm:p-6 [&:nth-child(-n+2)]:border-b [&:nth-child(odd)]:border-r lg:[&:nth-child(-n+2)]:border-b-0 lg:[&:not(:last-child)]:border-r">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-50 text-brand">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    </span>
                    <div class="min-w-0">
                        <dt class="text-[15px] font-bold leading-snug text-ink sm:text-base">{{ $title }}</dt>
                        <dd class="mt-1 text-[13px] leading-snug text-muted sm:text-sm">{{ $note }}</dd>
                    </div>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Form + what happens next --}}
    <section class="border-y border-line bg-mist">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8 lg:py-20">
            <div id="partner-form" class="scroll-mt-24 lg:col-span-7">
                <div class="rounded-2xl border border-line bg-white p-6 shadow-[0_24px_60px_-40px_rgba(20,20,20,.35)] sm:p-10"
                    x-data="backgroundForm">
                    <div x-data="{
                        step: 1, total: 3, hint: '',
                        stage: @js(old('stage', '')), type: @js(old('type', '')), city: @js(old('city', '')), company: @js(old('company', '')), name: @js(old('name', '')),
                        required: @js([1 => $stages ? ['stage'] : [], 2 => [...($types ? ['type'] : []), 'city', 'company']]),
                        go(n) {
                            const missing = n > this.step && (this.required[this.step] ?? []).some(f => ! String(this[f]).trim());
                            this.hint = missing ? 'Please complete this step to continue.' : '';
                            if (! missing) this.step = n;
                        },
                    }">
                        {{-- Sent --}}
                        <div x-cloak x-show="done || @js($sent)" role="status" class="py-8"
                            x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <span class="grid h-14 w-14 place-items-center rounded-full bg-brand-50 text-brand">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <h2 class="text-section mt-6 font-bold tracking-tight text-ink">Request sent</h2>
                            <p class="mt-3 max-w-md text-lg leading-relaxed text-ink-soft">Thank you<span x-show="name.trim()">, <span x-text="name.trim().split(' ')[0]"></span></span>. A partnerships advisor will call you to talk about <span x-text="company.trim() || 'your project'"></span>.</p>
                        </div>

                        <form x-show="! done && ! @js($sent)" method="POST" action="{{ route('partner.store') }}" @submit.prevent="step === total ? submit($el) : go(step + 1)" novalidate>
                            @csrf
                            <div class="absolute -left-[9999px]" aria-hidden="true">
                                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                            </div>

                            <h2 class="text-section font-bold tracking-tight text-ink">Tell us about your project</h2>
                            <p class="mt-2 text-muted">Three short steps. No commitment. We’ll call you to talk it through.</p>

                            <div class="mt-7 grid grid-cols-3 gap-2" aria-hidden="true">
                                @foreach (range(1, 3) as $n)
                                    <span class="h-1 rounded-full transition-colors duration-500" :class="step >= {{ $n }} ? 'bg-brand' : 'bg-line'"></span>
                                @endforeach
                            </div>
                            <p class="mt-6 text-sm text-muted">Step <span x-text="step">1</span> of 3</p>

                            {{-- Step 1 --}}
                            <fieldset x-show="step === 1" class="mt-2">
                                <legend class="text-xl font-semibold text-ink">What stage is the project at?</legend>
                                <div class="mt-5 space-y-3">
                                    @forelse ($stages as $stageSlug => $stageName)
                                        <label class="block cursor-pointer">
                                            <input type="radio" name="stage" value="{{ $stageSlug }}" x-model="stage" class="peer sr-only">
                                            <span class="flex items-center justify-between gap-4 rounded-xl border border-line px-5 py-4 transition duration-200 hover:border-ink/30 peer-checked:border-ink peer-checked:bg-ink peer-focus-visible:ring-4 peer-focus-visible:ring-ink/10">
                                                <span>
                                                    <span class="block font-semibold" :class="stage === @js($stageSlug) ? 'text-white' : 'text-ink'">{{ $stageName }}</span>
                                                </span>
                                                <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full border-2 transition-colors" :class="stage === @js($stageSlug) ? 'border-brand bg-brand' : 'border-line'">
                                                    <svg x-show="stage === @js($stageSlug)" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3"><path d="m4.5 10.5 3.5 3.5 7.5-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </span>
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-muted">You can tell us about the project's stage in your message.</p>
                                    @endforelse
                                </div>
                            </fieldset>

                            {{-- Step 2 --}}
                            <fieldset x-show="step === 2" x-cloak class="mt-2">
                                <legend class="text-xl font-semibold text-ink">About the project</legend>
                                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                    {{-- From the admin's Property types; not asked while that list is empty --}}
                                    @if ($types)
                                        <label class="block">
                                            <span class="{{ $label }}">Project type</span>
                                            <select x-combo name="type" x-model="type" class="{{ $field }} field-select">
                                                <option value="">Choose a type</option>
                                                @foreach ($types as $value => $text)
                                                    <option value="{{ $value }}">{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                    <label class="block">
                                        <span class="{{ $label }}">City</span>
                                        <select x-combo name="city" x-model="city" class="{{ $field }} field-select">
                                            <option value="">Choose a city</option>
                                            {{-- Every city in Pakistan, by province: a builder may launch where we have no project yet --}}
                                            @foreach (\App\Support\PakistanCities::byProvince() as $province => $provinceCities)
                                                <optgroup label="{{ $province }}">
                                                    @foreach ($provinceCities as $slug => $cityName)
                                                        <option value="{{ $slug }}" @selected(old('city') === $slug)>{{ $cityName }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="{{ $label }}">Company name</span>
                                        <input type="text" name="company" x-model="company" autocomplete="organization" placeholder="e.g. ABC Builders" class="{{ $field }}">
                                    </label>
                                    <label class="block">
                                        <span class="{{ $label }}">Project name <span class="font-normal text-muted">(optional)</span></span>
                                        <input type="text" name="project" value="{{ old('project') }}" class="{{ $field }}">
                                    </label>
                                </div>
                            </fieldset>

                            {{-- Step 3 --}}
                            <fieldset x-show="step === 3" x-cloak class="mt-2">
                                <legend class="text-xl font-semibold text-ink">How can we reach you?</legend>
                                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="{{ $label }}">Your name</span>
                                        <input type="text" name="name" x-model="name" autocomplete="name" class="{{ $field }}">
                                    </label>
                                    <label class="block">
                                        <span class="{{ $label }}">Phone number</span>
                                        <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="0300 1234567" class="{{ $field }}">
                                    </label>
                                    <label class="block sm:col-span-2">
                                        <span class="{{ $label }}">Email <span class="font-normal text-muted">(optional)</span></span>
                                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="{{ $field }}">
                                    </label>
                                    <label class="block sm:col-span-2">
                                        <span class="{{ $label }}">Anything else? <span class="font-normal text-muted">(optional)</span></span>
                                        <textarea name="message" rows="3" placeholder="Launch date, number of floors, what you need from a partner" class="{{ $field }} resize-none">{{ old('message') }}</textarea>
                                    </label>
                                </div>
                            </fieldset>

                            <p x-show="hint" x-text="hint" x-cloak class="mt-5 text-sm font-semibold text-brand" role="alert"></p>
                            <p x-show="error" x-text="error" x-cloak class="mt-5 text-sm font-semibold text-brand" role="alert"></p>
                            @if ($errors->any())
                                <p x-show="! error && ! hint" class="mt-5 text-sm font-semibold text-brand" role="alert">{{ $errors->first() }}</p>
                            @endif

                            <div class="mt-8 flex items-center gap-3">
                                <button type="button" x-show="step > 1" x-cloak @click="hint = ''; step--"
                                    class="btn btn-outline">Back</button>
                                <button type="submit" :disabled="sending"
                                    class="btn btn-primary focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand/30 disabled:cursor-wait disabled:opacity-75">
                                    <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                    <span x-text="sending ? 'Sending…' : (step === total ? 'Send request' : 'Continue')">Continue</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 lg:pt-4">
                <h2 class="text-card-title font-bold tracking-tight text-ink">What happens next</h2>
                <ol class="mt-7 space-y-7">
                    @foreach ($steps as $i => [$title, $text])
                        <li class="flex gap-4">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full border border-line bg-white text-sm font-semibold text-ink">{{ $i + 1 }}</span>
                            <div>
                                <p class="font-semibold text-ink">{{ $title }}</p>
                                <p class="mt-1 leading-relaxed text-ink-soft">{{ $text }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-9 rounded-[14px] border border-line bg-white p-6">
                    <p class="font-semibold text-ink">Your buyers book with you</p>
                    <p class="mt-2 leading-relaxed text-ink-soft">Bookings and agreements are always made directly with the builder. We bring the buyer to your sales team and support them through it.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Partners --}}
    @if ($partners->isNotEmpty())
        <section class="bg-ink text-white">
            <div class="py-section-sm mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-12 lg:items-center lg:gap-10 lg:px-8">
                <div class="lg:col-span-4">
                    <h2 class="text-section font-bold tracking-tight">Builders who work with us</h2>
                    <p class="text-lead mt-3 leading-relaxed text-white/65">Some of the builders whose projects we market and sell on Mark Properties and AbadRaho.com.</p>
                    <a href="{{ route('developers.index') }}" class="hit-area mt-5 inline-block font-semibold text-white underline decoration-brand decoration-2 underline-offset-[6px] hover:text-brand">See all developers</a>
                </div>
                {{-- Four across on every screen (two tall columns made the phone page long) --}}
                <ul class="grid grid-cols-4 gap-2 sm:gap-3 lg:col-span-8">
                    @foreach ($partners as $partner)
                        <li>
                            <a href="{{ route('developers.show', $partner['slug']) }}" class="group relative grid h-20 place-items-center rounded-xl bg-white px-2 transition-transform duration-300 hover:-translate-y-0.5 sm:h-28 sm:rounded-[14px] sm:px-5" title="{{ $partner['name'] }}">
                                <x-builder-logo :developer="$partner" size="clamp(2.2rem, 1.6rem + 2.1vw, 3.7rem)" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Questions: one accordion (like the home FAQ); every answer, step and checklist stays in the page --}}
    <section class="py-section-sm mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-4">
                <h2 class="text-section font-bold tracking-tight text-ink">Partnering with us, answered</h2>
                <p class="text-lead mt-3 leading-relaxed text-ink-soft">How we work with builders, from the first call to bookings.</p>
            </div>

            <ul class="space-y-3 lg:col-span-8" x-data="{ active: 0 }">
                @foreach ($questions as $i => $item)
                    <li id="{{ $item['id'] }}" class="scroll-mt-28 overflow-hidden rounded-2xl border bg-white transition-all duration-500 ease-out-expo"
                        :class="active === {{ $i }} ? 'border-transparent shadow-[0_24px_48px_-28px_rgba(20,20,20,.35)]' : 'border-line hover:border-ink/25'">
                        <h3>
                            <button type="button" id="pq-{{ $i }}" aria-controls="pa-{{ $i }}" :aria-expanded="active === {{ $i }}"
                                @click="active = active === {{ $i }} ? null : {{ $i }}"
                                class="group flex w-full items-center justify-between gap-5 px-5 py-4 text-left sm:px-7 sm:py-5">
                                <span class="text-base font-bold leading-snug transition-colors duration-300 sm:text-[17px]"
                                    :class="active === {{ $i }} ? 'text-brand' : 'text-ink group-hover:text-brand'">{{ $item['q'] }}</span>
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full transition-all duration-500 ease-out-expo"
                                    :class="active === {{ $i }} ? 'rotate-45 bg-brand text-white' : 'bg-mist text-ink group-hover:bg-ink group-hover:text-white'" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12" stroke-linecap="round"/></svg>
                                </span>
                            </button>
                        </h3>
                        <div id="pa-{{ $i }}" role="region" aria-labelledby="pq-{{ $i }}" x-show="active === {{ $i }}" x-collapse @if ($i) x-cloak @endif>
                            <div class="px-5 pb-6 text-[15px] leading-relaxed text-ink-soft sm:px-7">
                                <p class="text-ink">{{ $item['short'] }}</p>
                                @isset($item['body'])
                                    <p class="mt-3">{{ $item['body'] }}</p>
                                @endisset
                                @isset($item['steps'])
                                    <ol class="mt-5 space-y-4 border-t border-line pt-5">
                                        @foreach ($item['steps'] as $n => [$title, $text])
                                            <li class="flex gap-3">
                                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-ink text-[11px] font-semibold text-white">{{ $n + 1 }}</span>
                                                <p><span class="font-semibold text-ink">{{ $title }}.</span> {{ $text }}</p>
                                            </li>
                                        @endforeach
                                    </ol>
                                @endisset
                                @isset($item['list'])
                                    <ul class="mt-5 grid gap-x-6 gap-y-2.5 border-t border-line pt-5 sm:grid-cols-2">
                                        @foreach ($item['list'] as $point)
                                            <li class="flex gap-2.5">{!! $check !!}{{ $point }}</li>
                                        @endforeach
                                    </ul>
                                @endisset
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Closing --}}
    <section class="bg-ink text-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-16 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <h2 class="text-section-lg font-bold tracking-tight">Launching a project soon?</h2>
                <p class="mt-3 leading-relaxed text-white/65">Tell us about it and a partnerships advisor will call you to talk it through.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="#partner-form" class="btn btn-primary">Tell us about your project</a>
            </div>
        </div>
    </section>
</x-layouts.app>
