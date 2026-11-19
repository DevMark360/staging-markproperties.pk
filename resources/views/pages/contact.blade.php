@php
    $contact = config('site.contact');
    $map = config('site.map');
    $sent = (bool) session('contact_sent');
    // Same white outlined fields as the Partner, project and policy forms (C-03)
    $field = 'mt-2 block w-full rounded-xl border border-line bg-white px-4 py-3 text-[15px] text-ink transition duration-200 placeholder:text-muted/60 hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5';
    $label = 'text-[13px] font-semibold text-ink';
    // Contact rows in the dark panel: [label, value, href, icon path]
    $rows = array_filter([
        $contact['email'] ? ['Email', $contact['email'], 'mailto:'.$contact['email'], 'M3 6.5A1.5 1.5 0 0 1 4.5 5h15A1.5 1.5 0 0 1 21 6.5v11a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-11Zm0 .5 9 6 9-6'] : null,
        ['Office hours', $contact['hours'], null, 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        // The address shows once, under the office map below; here only when there's no map
        $map ? null : ['Visit us', $contact['address'], null, 'M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Zm0-9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
    ]);
@endphp

<x-layouts.app title="Contact Us"
    description="Send Mark Properties a message and an advisor will call you back about off-plan projects, site visits or builder partnerships across Pakistan.">

    <section class="bg-mist">
        <div class="mx-auto max-w-7xl px-4 pb-16 pt-10 sm:px-6 sm:pt-12 lg:px-8 lg:pb-24">
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-sm text-muted">
                    <li><a href="{{ route('home') }}" class="transition-colors hover:text-ink">Home</a></li>
                    <li aria-hidden="true"><svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></li>
                    <li class="text-ink" aria-current="page">Contact</li>
                </ol>
            </nav>

            <div class="mt-8 grid overflow-hidden rounded-3xl bg-white shadow-[0_30px_80px_-40px_rgba(20,20,20,.35)] ring-1 ring-line lg:grid-cols-12">

                {{-- Dark panel --}}
                {{-- Phones/tablets: the form comes first and this panel after it (C-01); side by side, panel left, from 1024px --}}
                <div class="relative isolate order-2 overflow-hidden bg-ink px-6 py-10 text-white sm:px-10 sm:py-12 lg:order-none lg:col-span-5">
                    {{-- Brand roofline, echoing the logo mark --}}
                    <svg class="pointer-events-none absolute -bottom-20 -right-20 -z-10 h-72 w-72 text-brand opacity-25" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                        <path d="M20 120 100 50l80 70" stroke="currentColor" stroke-width="14" stroke-linejoin="round"/>
                        <path d="M48 150 100 104l52 46" stroke="currentColor" stroke-opacity=".5" stroke-width="14" stroke-linejoin="round"/>
                    </svg>

                    <h1 class="text-page-title leading-[1.1] font-medium">Talk to us</h1>
                    <p class="mt-4 max-w-sm leading-relaxed text-white/65">Questions about a project, a site visit or partnering with us? Send us a message and an advisor will call you back.</p>

                    <ul class="mt-10 space-y-6">
                        @foreach ($rows as [$title, $value, $href, $icon])
                            <li class="flex gap-4">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white/[.08] text-brand ring-1 ring-white/10">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                                </span>
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-[13px] text-white/50">{{ $title }}</p>
                                    @if ($href)
                                        <a href="{{ $href }}" @if (! str_starts_with($href, 'mailto:')) target="_blank" rel="noopener" @endif
                                            class="mt-0.5 block break-words font-semibold leading-snug text-white transition-colors hover:text-brand">{{ $value }}</a>
                                    @else
                                        <p class="mt-0.5 font-semibold leading-snug">{{ $value }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if ($social = config('site.social'))
                        <div class="mt-12 flex flex-wrap gap-2">
                            @foreach ($social as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="rounded-full border border-white/15 px-4 py-2 text-[13px] font-medium text-white/80 transition-colors hover:border-white/40 hover:text-white">{{ $network }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Form --}}
                <div class="order-1 px-6 py-10 sm:px-10 sm:py-12 lg:order-none lg:col-span-7 lg:px-14">
                    <div id="message" x-data="backgroundForm" class="scroll-mt-28">
                        <div x-data="{ name: @js(old('name', '')), phone: @js(old('phone', '')), topic: @js(old('topic', $topic)),
                            city: @js(old('city', '')), area: @js(old('area', '')), locations: @js($locations) }">

                            {{-- Sent: the confirmation, then a card leading to the projects in the area they chose --}}
                            @php($projectsLink = session('contact_projects'))
                            <div x-cloak x-show="done || @js($sent)" role="status" class="flex flex-col justify-center lg:min-h-[28rem]"
                                x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="flex items-start gap-4 sm:gap-5">
                                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-brand text-white shadow-[0_12px_28px_-12px_rgba(220,26,33,.9)] sm:h-14 sm:w-14">
                                        <svg class="h-6 w-6 sm:h-7 sm:w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <div class="min-w-0 pt-0.5">
                                        <h2 class="text-section font-bold tracking-tight text-ink">Message sent</h2>
                                        <p class="mt-2 max-w-md text-lead leading-relaxed text-ink-soft">
                                            Thanks<span x-show="name.trim()">, <span x-text="name.trim().split(' ')[0]"></span></span>. An advisor will call you<span x-show="phone.trim()"> on <strong class="whitespace-nowrap font-semibold text-ink" x-text="phone.trim()"></strong></span> soon.
                                        </p>
                                    </div>
                                </div>

                                {{-- While they wait: the chosen area's projects (or the city's, or all, when it has none listed) --}}
                                <a x-show="result?.projects || @js((bool) $projectsLink)" :href="result?.projects?.url ?? @js($projectsLink['url'] ?? null)" href="{{ $projectsLink['url'] ?? route('projects.index') }}"
                                    class="group mt-8 flex items-center gap-4 rounded-2xl border border-line bg-mist p-4 transition-colors duration-300 hover:border-ink/20 hover:bg-white sm:p-5">
                                    <span class="hidden h-12 w-12 shrink-0 place-items-center rounded-xl bg-white text-brand ring-1 ring-line min-[400px]:grid">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-semibold text-muted">While you wait</span>
                                        <span class="mt-0.5 block font-bold text-ink" x-text="result?.projects?.title ?? @js($projectsLink['title'] ?? 'New projects')">{{ $projectsLink['title'] ?? 'New projects' }}</span>
                                        <span class="mt-0.5 block text-sm leading-snug text-muted" x-text="result?.projects?.note ?? @js($projectsLink['note'] ?? '')">{{ $projectsLink['note'] ?? '' }}</span>
                                    </span>
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand text-white transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="sr-only" x-text="result?.projects?.label ?? @js($projectsLink['label'] ?? '')"></span>
                                </a>

                                <button type="button" x-show="done"
                                    @click="done = false; result = null; name = ''; phone = ''; city = ''; area = ''; $nextTick(() => $root.querySelector('form').reset())"
                                    class="hit-area mt-6 self-start text-sm font-semibold text-ink underline decoration-line underline-offset-4 transition-colors hover:decoration-brand">Send another message</button>
                            </div>

                            <form x-show="! done && ! @js($sent)" method="POST" action="{{ route('contact.store') }}" @submit.prevent="submit($el)" novalidate>
                                @csrf
                                <div class="absolute -left-[9999px]" aria-hidden="true">
                                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                                </div>

                                <h2 class="text-section font-bold tracking-tight text-ink">Send us a message</h2>
                                <p class="mt-2 text-muted">Fill in your details and we’ll get back to you.</p>

                                <fieldset class="mt-8">
                                    <legend class="{{ $label }}">I’m interested in</legend>
                                    <div class="mt-3 grid grid-cols-2 gap-2.5">
                                        @foreach ($topics as $value => $text)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="topic" value="{{ $value }}" x-model="topic" class="peer sr-only">
                                                <span class="flex items-center gap-2.5 rounded-xl border border-line px-4 py-3 text-sm font-medium text-ink-soft transition duration-200 hover:border-ink/30 peer-checked:border-brand peer-checked:bg-brand-50 peer-checked:text-ink peer-focus-visible:ring-4 peer-focus-visible:ring-brand/15">
                                                    <span class="grid h-4 w-4 shrink-0 place-items-center rounded-full border-2 transition-colors" :class="topic === @js($value) ? 'border-brand' : 'border-line'">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-brand transition-transform duration-200" :class="topic === @js($value) ? 'scale-100' : 'scale-0'"></span>
                                                    </span>
                                                    {{ $text }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>

                                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="{{ $label }}">Full name</span>
                                        <input type="text" name="name" x-model="name" required autocomplete="name" placeholder="Your name" class="{{ $field }}">
                                    </label>
                                    <label class="block">
                                        <span class="{{ $label }}">Phone number</span>
                                        <input type="tel" name="phone" x-phone @phone="phone = $event.detail.readable" required autocomplete="tel" inputmode="tel" placeholder="0300 1234567" class="{{ $field }}">
                                    </label>
                                    {{-- City, then that city's areas (from the admin; only cities and areas with live projects). Both required (C-06). --}}
                                    <label class="block">
                                        <span class="{{ $label }}">City</span>
                                        <select x-combo name="city" x-model="city" @change="area = ''" required class="{{ $field }} field-select">
                                            <option value="">Choose a city</option>
                                            @foreach ($locations as $slug => $place)
                                                <option value="{{ $slug }}" @selected(old('city') === $slug)>{{ $place['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="{{ $label }}">Area</span>
                                        <select x-combo name="area" x-model="area" required :disabled="! city" class="{{ $field }} field-select disabled:cursor-not-allowed disabled:bg-mist disabled:text-muted"
                                            x-init="$el.querySelectorAll('[data-no-js]').forEach((group) => group.remove())">
                                            <option value="" x-text="city ? 'Choose an area' : 'Choose a city first'">Choose an area</option>
                                            {{-- Without JavaScript: every area, grouped by city (the server checks it matches the city). Alpine removes these and lists the chosen city's areas instead (T4) --}}
                                            @foreach ($locations as $citySlug => $place)
                                                <optgroup label="{{ $place['name'] }}" data-no-js>
                                                    @foreach ($place['areas'] as $areaSlug => $areaName)
                                                        <option value="{{ $areaSlug }}" @selected(old('area') === $areaSlug && old('city') === $citySlug)>{{ $areaName }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                            <template x-for="(name, slug) in (locations[city]?.areas ?? {})" :key="slug">
                                                <option :value="slug" x-text="name" :selected="slug === area"></option>
                                            </template>
                                            <option value="{{ \App\Support\ContactLocations::NOT_SURE }}">{{ \App\Support\ContactLocations::NOT_SURE_LABEL }}</option>
                                        </select>
                                    </label>
                                    <label class="block sm:col-span-2">
                                        <span class="{{ $label }}">Email <span class="font-normal text-muted">(optional)</span></span>
                                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" class="{{ $field }}">
                                    </label>
                                </div>

                                <label class="mt-5 block">
                                    <span class="{{ $label }}">Message <span class="font-normal text-muted">(optional)</span></span>
                                    <textarea name="message" rows="4" placeholder="Budget, preferred area, timing…" class="{{ $field }} resize-none">{{ old('message') }}</textarea>
                                </label>

                                <button type="submit" :disabled="sending"
                                    class="btn btn-primary mt-7 w-full py-4 text-base disabled:cursor-wait disabled:opacity-75">
                                    <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                    <span x-text="sending ? 'Sending…' : 'Send message'">Send message</span>
                                </button>

                                <p x-show="error" x-text="error" x-cloak class="mt-4 text-sm font-semibold text-brand" role="alert"></p>
                                @if ($errors->any())
                                    <p x-show="! error" class="mt-4 text-sm font-semibold text-brand" role="alert">{{ $errors->first() }}</p>
                                @endif

                                <p class="mt-4 text-center text-xs leading-relaxed text-muted">By sending, you agree to be contacted by Mark Properties. <a href="{{ route('privacy') }}" class="underline underline-offset-2 hover:text-ink">Privacy policy</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Map --}}
    @if ($map)
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <h2 class="text-section font-bold tracking-tight text-ink">Visit our Karachi office</h2>
            {{-- One card: the map, then the address with "Get directions" (the address appears once on this page, plus the footer) --}}
            <div class="mt-8 overflow-hidden rounded-3xl border border-line bg-white">
                <x-map-embed :src="$map['embed']" title="Map showing the Mark Properties office" class="h-[22rem] ring-0 sm:h-[26rem]" />
                <div class="flex flex-col gap-4 border-t border-line p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <address class="flex items-start gap-3 text-[15px] not-italic leading-relaxed text-ink">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        <span>{{ $contact['address'] }}</span>
                    </address>
                    <a href="{{ $map['link'] }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm shrink-0 self-start sm:self-auto">Get directions</a>
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
