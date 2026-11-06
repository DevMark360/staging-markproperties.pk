@php
    $contact = config('site.contact');
    $map = config('site.map');
    $partners = \App\Support\BuilderStrip::for('about'); // different builders from the Home and Partner strips

    $values = [
        ['Our mission', 'Deliver residential and commercial off-plan opportunities that match real buyer needs, backed by competent advisors and an integrated team from search to closing.', '<path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><circle cx="12" cy="12" r=".8" fill="currentColor"/>'],
        ['Our vision', 'Become Pakistan’s most trusted digital gateway for off-plan property, expanding reach while keeping every client relationship transparent and accountable.', '<path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z" stroke-linejoin="round"/><circle cx="12" cy="12" r="3"/>'],
        ['Our core values', 'Communication, commitment and client care guide every recommendation. We provide holistic real estate advice, not just listings.', '<path d="M12 20.5s-7.5-4.4-7.5-10.1A4.4 4.4 0 0 1 12 7.6a4.4 4.4 0 0 1 7.5 2.8c0 5.7-7.5 10.1-7.5 10.1Z" stroke-linejoin="round"/>'],
    ];


    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateAgent',
        'name' => config('site.name'),
        'url' => route('home'),
        'logo' => asset('images/logo-dark.png'),
        'image' => asset('images/about/office.webp'),
        'description' => 'Mark Properties is the Karachi real estate advisory behind AbadRaho.com, helping buyers find, evaluate and book new off-plan projects across Pakistan.',
        'founder' => ['@type' => 'Person', 'name' => 'Adnan Tumbi', 'jobTitle' => 'CEO and Founder'],
        'email' => $contact['email'],
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => $contact['address'], 'addressLocality' => 'Karachi', 'addressCountry' => 'PK'],
        'openingHoursSpecification' => ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'], 'opens' => '10:00', 'closes' => '19:00'],
        'sameAs' => array_values(config('site.social')),
    ]); // drops empty values, e.g. no telephone
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

<x-layouts.app title="About Us" :image="asset('images/about/office.webp')"
    description="Mark Properties is the Karachi property advisory behind AbadRaho.com, helping buyers find, compare and book new off-plan projects across Pakistan.">

    <x-page-header title="Off-plan property advisors for buyers and builders" breadcrumb="About us"
        lead="Mark Properties is the Karachi real estate advisory behind AbadRaho.com. We help buyers find, evaluate and book new residential and commercial projects from trusted builders.">
        <div class="btn-pair mt-8">
            <a href="{{ route('contact') }}" class="btn btn-primary">Talk to an advisor</a>
            <a href="{{ route('projects.index') }}" class="btn btn-outline">Explore projects</a>
        </div>
    </x-page-header>

    {{-- ───────────── Who we are ───────────── --}}
    <section class="py-section-sm mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Stacked (heading, text, then the four points in a row) so there's no empty column beside a lone heading --}}
        <x-reveal>
            <h2 class="text-section font-bold tracking-tight text-ink">Advisors first, listings second</h2>
            <p class="text-lead mt-5 max-w-3xl leading-relaxed text-ink-soft">
                We work alongside Pakistan’s builders as their marketing and sales partner, and alongside buyers as their advisor. Every project we present is supported by trained property advisors who explain handover timelines and developer credentials in plain language, and who stay with you from first search to booking.
            </p>
        </x-reveal>
        <x-reveal :delay="100">
            <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['Off-plan specialists', 'Long-standing builder relationships', 'Advisor support from search to booking', 'Operators of AbadRaho.com'] as $point)
                    <li class="flex items-center gap-3 rounded-[14px] border border-line bg-white p-4">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-50 text-brand">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m5 10.5 3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="text-sm font-semibold text-ink">{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
        </x-reveal>
    </section>

    {{-- ───────────── CEO message ───────────── --}}
    <section class="py-section-sm bg-mist">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
            <x-reveal class="lg:col-span-5">
                <div class="group relative overflow-hidden rounded-[18px] shadow-[0_40px_70px_-40px_rgba(20,20,20,.55)]">
                    <img src="{{ asset('images/about/office.webp') }}" alt="Mark Properties, the AbadRaho real estate advisory team" width="1200" height="900" loading="lazy"
                        class="aspect-[4/5] w-full object-cover object-center transition-transform duration-[1200ms] ease-out-expo group-hover:scale-105">
                    <span class="absolute inset-x-0 bottom-0 h-1 bg-brand" aria-hidden="true"></span>
                </div>
            </x-reveal>
            <x-reveal :delay="120" class="lg:col-span-7">
                <h2 class="text-section font-bold tracking-tight text-ink">Message from our CEO</h2>
                <blockquote class="relative mt-8 rounded-[18px] bg-ink p-7 text-white">
                    <svg class="absolute -top-4 left-6 h-9 w-9 text-brand" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.6 6C6.5 7.3 4.5 10.1 4.5 13.6c0 2.5 1.5 4.4 3.6 4.4 1.9 0 3.3-1.4 3.3-3.2 0-1.7-1.2-3-2.9-3.1.3-1.6 1.5-3 3.2-3.9L9.6 6Zm8.5 0c-3.1 1.3-5.1 4.1-5.1 7.6 0 2.5 1.5 4.4 3.6 4.4 1.9 0 3.3-1.4 3.3-3.2 0-1.7-1.2-3-2.9-3.1.3-1.6 1.5-3 3.2-3.9L18.1 6Z"/></svg>
                    <p class="text-xl font-semibold leading-snug">A leading real estate company in Pakistan, trusted by off-plan buyers.</p>
                </blockquote>
                <div class="mt-8 space-y-4 text-[15px] leading-relaxed text-ink-soft">
                    <p>Mark Properties has grown into one of Pakistan’s trusted real estate advisory firms. Since our founding, we have helped buyers find off-plan residential and commercial projects and complete their purchases with clear documentation and ethical guidance.</p>
                    <p>Our clients return to us because we combine market expertise with responsive service. Every project on AbadRaho is supported by trained property advisors who explain handover timelines and developer credentials in plain language.</p>
                    <p>Our reputation is built on transparency, honest communication and consistent follow-through, whether you are buying an apartment in Karachi, a plot in a growing corridor, or a commercial unit for rental income.</p>
                </div>
                <div class="mt-8 flex items-center gap-4 border-t border-line pt-6">
                    <span class="grid h-12 w-12 place-items-center rounded-full bg-brand text-lg font-extrabold text-white">AT</span>
                    <span>
                        <span class="block font-bold text-ink">Adnan Tumbi</span>
                        <span class="text-sm text-muted">CEO and Founder, Mark Properties</span>
                    </span>
                </div>
            </x-reveal>
        </div>
    </section>

    {{-- ───────────── Mission, vision, values ───────────── --}}
    <section class="py-section-sm relative overflow-hidden bg-ink text-white">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-reveal>
                <x-section-header :dark="true" title="Mission, vision and values" />
            </x-reveal>
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ($values as [$heading, $text, $icon])
                    <x-reveal :delay="$loop->index * 110" class="h-full">
                        <div class="h-full rounded-[14px] border border-white/10 bg-white/[0.03] p-7">
                            <span class="grid h-12 w-12 place-items-center rounded-xl bg-brand text-white">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $icon !!}</svg>
                            </span>
                            <h3 class="mt-8 text-xl font-bold">{{ $heading }}</h3>
                            <p class="mt-3 text-[15px] leading-relaxed text-white/60">{{ $text }}</p>
                        </div>
                    </x-reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ───────────── Partners ───────────── --}}
    @if ($partners->isNotEmpty())
        <section class="py-section-sm border-t border-line bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-reveal>
                    <x-section-header title="Builders we work with" action="All developers" :href="route('developers.index')" />
                </x-reveal>
                <ul class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4 xl:grid-cols-8">
                    @foreach ($partners as $developer)
                        <li>
                            <x-reveal :delay="$loop->index * 60" class="h-full">
                                <a href="{{ route('developers.show', $developer['slug']) }}" title="{{ $developer['name'] }}"
                                    class="group relative grid h-28 place-items-center overflow-hidden rounded-2xl bg-mist px-5 transition-all duration-500 ease-out-expo hover:-translate-y-1 hover:bg-white hover:shadow-[0_22px_40px_-22px_rgba(20,20,20,.35)] hover:ring-1 hover:ring-line">
                                    <x-builder-logo :developer="$developer" size="clamp(2.2rem, 1.6rem + 2.1vw, 3.7rem)" class="group-hover:-translate-y-2" />
                                    <span class="absolute inset-x-2 bottom-2 translate-y-2 truncate text-center text-xs font-semibold text-ink-soft opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">{{ $developer['name'] }}</span>
                                </a>
                            </x-reveal>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ───────────── Visit us ───────────── --}}
    <section class="py-section-sm bg-mist">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">
            <x-reveal class="lg:col-span-5">
                <h2 class="text-section font-bold tracking-tight text-ink">Our Karachi office</h2>
                {{-- The address is shown once, with the map (and in the footer); this list has hours and email --}}
                <ul class="mt-8 space-y-3">
                    @foreach (array_filter([
                        ['Office hours', $contact['hours'], null, '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2" stroke-linecap="round"/>'],
                        ['Email', $contact['email'], $contact['email'] ? 'mailto:'.$contact['email'] : null, '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6" stroke-linejoin="round"/>'],
                    ], fn ($row) => $row[1]) as [$label, $value, $href, $icon])
                        <li class="flex items-start gap-4 rounded-[14px] border border-line bg-white p-4">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-50 text-brand">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $icon !!}</svg>
                            </span>
                            <span>
                                <span class="block text-sm text-muted">{{ $label }}</span>
                                @if ($href)
                                    <a href="{{ $href }}" class="mt-0.5 block font-semibold text-ink transition-colors hover:text-brand">{{ $value }}</a>
                                @else
                                    <span class="mt-0.5 block font-semibold leading-relaxed text-ink">{{ $value }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-reveal>
            @if ($map)
                <x-reveal :delay="120" class="lg:col-span-7">
                    <div class="flex h-full flex-col overflow-hidden rounded-[18px] border border-line bg-white">
                        <x-map-embed :src="$map['embed']" :title="config('site.name').' office location'" class="min-h-72 flex-1 ring-0" />
                        <div class="flex flex-col gap-4 border-t border-line p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                            <p class="flex items-start gap-3 text-[15px] leading-relaxed text-ink">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                                <span>{{ $contact['address'] }}</span>
                            </p>
                            <a href="{{ $map['link'] }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm shrink-0 self-start sm:self-auto">Get directions</a>
                        </div>
                    </div>
                </x-reveal>
            @endif
        </div>
    </section>
</x-layouts.app>
