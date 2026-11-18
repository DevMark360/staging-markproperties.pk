{{-- ───────────── Launch alerts ───────────── --}}
<section id="alerts" class="py-section-sm bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-reveal>
            <div class="relative isolate overflow-hidden rounded-[2rem] bg-ink px-6 py-12 text-white sm:px-12 sm:py-16 lg:px-16">
                <div class="absolute inset-0 -z-10" aria-hidden="true">
                    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand/35 blur-3xl"></div>
                    <div class="absolute -bottom-32 left-1/3 h-72 w-72 rounded-full bg-brand/15 blur-3xl"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:44px_44px] [mask-image:radial-gradient(ellipse_at_70%_30%,black,transparent_70%)]"></div>
                </div>

                <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
                    <div class="lg:col-span-6">
                        <p class="inline-flex items-center gap-2.5 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-semibold text-white/80">
                            <svg class="h-3.5 w-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9.5a6 6 0 1 1 12 0c0 6 2.5 7.5 2.5 7.5h-17S6 15.5 6 9.5Z" stroke-linejoin="round"/><path d="M10 20.5a2.2 2.2 0 0 0 4 0" stroke-linecap="round"/></svg>
                            Launch alerts
                        </p>
                        <h2 class="text-section-lg mt-5 text-balance font-extrabold leading-tight tracking-tight">New launches, before everyone else.</h2>
                        <p class="mt-4 max-w-lg text-base leading-relaxed text-white/65 sm:text-lg">Be the first to hear about pre-launch projects across Pakistan, by email or phone.</p>
                        <ul class="mt-7 flex flex-wrap gap-x-6 gap-y-2 text-sm text-white/70">
                            @foreach (['Pre-launch news first', 'Only new projects', 'No spam'] as $point)
                                <li class="flex items-center gap-2">
                                    <span class="grid h-5 w-5 place-items-center rounded-full bg-brand/20 text-brand">
                                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m5 10.5 3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="lg:col-span-6" x-data="launchAlerts">
                        {{-- Success --}}
                        <div @unless (session('alert_subscribed')) x-cloak @endunless x-show="done || {{ session('alert_subscribed') ? 'true' : 'false' }}"
                            x-transition:enter="transition duration-700 ease-out-expo" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="rounded-3xl border border-white/10 bg-white/5 p-8 text-center backdrop-blur-md" role="status">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand text-white shadow-[0_12px_28px_-10px_rgba(220,26,33,.9)]">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <p class="mt-5 text-xl font-bold">You're on the list</p>
                            <p class="mt-2 text-sm text-white/65">We'll let you know when new projects launch.</p>
                        </div>

                        {{-- Form --}}
                        <form x-show="! done && ! {{ session('alert_subscribed') ? 'true' : 'false' }}" @if (session('alert_subscribed')) hidden @endif method="POST" action="{{ route('alerts.store') }}"
                            @submit.prevent="submit($el)" novalidate style="--field-error: #ff8a8f" {{-- lighter red, readable on the dark card --}}
                            class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur-md sm:p-6">
                            @csrf
                            <div class="absolute -left-[9999px]" aria-hidden="true">
                                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block sm:col-span-2">
                                    <span class="sr-only">Email address</span>
                                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Your email address"
                                        class="w-full rounded-xl border border-white/15 bg-white px-4 py-3.5 text-sm text-ink placeholder:text-muted transition-shadow duration-300 focus:outline-none focus:ring-4 focus:ring-brand/40">
                                </label>
                                <label class="block">
                                    <span class="sr-only">Phone number</span>
                                    <input type="tel" name="phone" x-phone value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="Phone number"
                                        class="w-full rounded-xl border border-white/15 bg-white px-4 py-3.5 text-sm text-ink placeholder:text-muted transition-shadow duration-300 focus:outline-none focus:ring-4 focus:ring-brand/40">
                                </label>
                                <label class="relative block">
                                    <span class="sr-only">City</span>
                                    <select x-combo name="city" class="w-full cursor-pointer appearance-none rounded-xl border border-white/15 bg-white/10 py-3.5 pe-10 ps-4 text-sm font-semibold text-white transition-colors duration-300 hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-brand/40">
                                        <option value="" class="text-ink">All Pakistan</option>
                                        @foreach ($cities as $slug => $name)
                                            <option value="{{ $slug }}" class="text-ink" @selected(old('city') === $slug)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-white/70" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </label>
                            </div>

                            <button type="submit" :disabled="sending"
                                class="group mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3.5 text-sm font-semibold text-white shadow-[0_14px_28px_-12px_rgba(220,26,33,.9)] transition-all duration-300 hover:-translate-y-px hover:bg-brand-700 active:translate-y-0 active:scale-[.98] disabled:cursor-wait disabled:opacity-75">
                                <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                <span x-text="sending ? 'Subscribing…' : 'Get launch alerts'">Get launch alerts</span>
                            </button>

                            {{-- Errors: from the background request, or from a full-page submit without JavaScript --}}
                            <p x-show="error" x-text="error" x-cloak class="mt-3 text-sm font-medium text-[#ff8a8f]" role="alert"></p>
                            @if ($errors->hasAny(['email', 'phone']))
                                <p x-show="! error" class="mt-3 text-sm font-medium text-[#ff8a8f]" role="alert">{{ $errors->first('email') ?: $errors->first('phone') }}</p>
                            @endif

                            <p class="mt-4 text-fine leading-relaxed text-white/60">By subscribing you agree to be contacted by Mark Properties about new projects. See our <a href="{{ route('privacy') }}" class="underline decoration-white/30 underline-offset-2 transition-colors hover:text-white">privacy policy</a>.</p>
                        </form>
                    </div>
                </div>
            </div>
        </x-reveal>
    </div>
</section>
