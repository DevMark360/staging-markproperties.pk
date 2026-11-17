{{--
    Property finder pop-up (home page). Opens after config('site.finder.delay') seconds with no activity, asks the
    admin's questions (Content → Property finder) one at a time, then for a name and number, and saves a lead
    (Leads → Property finder). Logic: resources/js/finder.js. Hidden when switched off or when there are no questions.
--}}
@php
    $finderQuestions = config('site.finder.enabled') ? \App\Models\FinderQuestion::forPopup() : [];
    $field = 'mt-2 block w-full rounded-xl border border-line bg-white px-4 py-3 text-[15px] text-ink transition duration-200 placeholder:text-muted/60 hover:border-ink/30 focus:border-ink focus:outline-none focus:ring-4 focus:ring-ink/5';
    $tick = '<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3"><path d="m4.5 10.5 3.5 3.5 7.5-8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

@if ($finderQuestions)
    <div x-data="finder(@js($finderQuestions), @js(['delay' => config('site.finder.delay'), 'days' => config('site.finder.days')]))"
        @keydown.escape.window="open && close()">
        <div x-cloak x-show="open" x-transition:leave="transition duration-200" data-finder role="dialog" aria-modal="true" aria-labelledby="finder-title"
                x-trap.inert.noscroll="open"
                class="fixed inset-0 z-[100] flex items-end justify-center sm:items-center sm:p-6">
                {{-- Backdrop: click to close --}}
                <div x-show="open" x-transition.opacity.duration.300ms @click="close()" class="absolute inset-0 bg-ink/55 backdrop-blur-[2px]"></div>

                {{-- The panel pops in: a little scale and rise with a soft overshoot-free ease; phones get a sheet from the bottom --}}
                <div x-show="open"
                    x-transition:enter="transition duration-500 ease-out-expo motion-reduce:duration-0"
                    x-transition:enter-start="opacity-0 translate-y-10 sm:translate-y-3 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition duration-200 ease-in motion-reduce:duration-0"
                    x-transition:leave-start="opacity-100 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                    @keydown.enter="enter($event)"
                    class="relative flex max-h-[92dvh] w-full flex-col overflow-hidden rounded-t-[1.5rem] bg-white shadow-[0_40px_120px_-30px_rgba(20,20,20,.55)] sm:max-h-[min(46rem,90dvh)] sm:max-w-4xl sm:rounded-[1.5rem]">

                    {{-- Header --}}
                    <div class="flex items-center gap-3 border-b border-line px-5 py-4 sm:px-8">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-50 text-brand">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5M5 9v12h14V9"/></svg>
                        </span>
                        <p class="min-w-0 flex-1 truncate text-sm font-semibold text-ink-soft">{{ config('site.finder.title') }}</p>
                        <button type="button" @click="close()" aria-label="Close"
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-ink-soft transition hover:bg-mist hover:text-ink focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-ink/10">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 5 10 10M15 5 5 15" stroke-linecap="round"/></svg>
                        </button>
                    </div>

                    {{-- Questions --}}
                    <div x-show="step < total()" class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 pb-6 pt-6 sm:px-8 sm:pt-8">
                        <template x-for="(q, i) in questions" :key="q.id">
                            {{-- Each question slides in from the side it comes from (CSS: .finder-step) --}}
                            <div x-show="step === i">
                                <div :class="forward ? 'finder-step-fwd' : 'finder-step-back'">
                                    <h2 :id="step === i ? 'finder-title' : null" x-text="q.title" class="text-card-title font-bold leading-tight tracking-tight text-ink sm:text-[clamp(1.5rem,1.1rem+1.2vw,2rem)]"></h2>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <span x-show="q.multiple" class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand">{!! $tick !!} You can choose more than one</span>
                                        <span x-show="q.hint && ! q.multiple" x-text="q.hint" class="text-sm text-muted"></span>
                                    </div>

                                    {{-- Photo cards when any option has a photo (one without gets a plain placeholder), otherwise simple buttons --}}
                                    <template x-if="q.choices.some(c => c.image)">
                                        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4" role="group" :aria-label="q.title">
                                            <template x-for="c in q.choices" :key="c.label">
                                                <button type="button" @click="choose(q, c.label)" :aria-pressed="isPicked(q.id, c.label)"
                                                    :class="isPicked(q.id, c.label) ? 'border-brand ring-2 ring-brand' : 'border-line hover:border-ink/30'"
                                                    class="group relative overflow-hidden rounded-2xl border bg-white text-left transition duration-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand/30">
                                                    <span class="grid aspect-[4/3] place-items-center overflow-hidden bg-mist text-ink/15">
                                                        <template x-if="c.image">
                                                            <img :src="c.image" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 ease-out-expo group-hover:scale-[1.04]">
                                                        </template>
                                                        <template x-if="! c.image">
                                                            <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5M5 9v12h14V9M10 21v-6h4v6"/></svg>
                                                        </template>
                                                    </span>
                                                    <span x-text="c.label" class="block px-3.5 py-3 text-[15px] font-semibold leading-snug text-ink"></span>
                                                    <span x-show="isPicked(q.id, c.label)" x-transition.scale.origin.center class="absolute right-2.5 top-2.5 grid h-7 w-7 place-items-center rounded-full bg-brand text-white shadow">{!! $tick !!}</span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="! q.choices.some(c => c.image)">
                                        <div class="mt-6 grid gap-3 sm:grid-cols-2" role="group" :aria-label="q.title">
                                            <template x-for="c in q.choices" :key="c.label">
                                                <button type="button" @click="choose(q, c.label)" :aria-pressed="isPicked(q.id, c.label)"
                                                    :class="isPicked(q.id, c.label) ? 'border-brand bg-brand-50' : 'border-line bg-white hover:border-ink/30'"
                                                    class="flex items-center gap-3.5 rounded-xl border px-4 py-4 text-left transition duration-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand/30">
                                                    <span :class="[q.multiple ? 'rounded-md' : 'rounded-full', isPicked(q.id, c.label) ? 'border-brand bg-brand text-white' : 'border-ink/25 text-transparent']"
                                                        class="grid h-5 w-5 shrink-0 place-items-center border-2 transition">{!! $tick !!}</span>
                                                    <span x-text="c.label" class="text-[15px] font-semibold text-ink"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Footer: progress, back and next --}}
                    <div x-show="step < total()" class="flex items-center gap-4 border-t border-line px-5 py-4 sm:px-8 sm:py-5">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline justify-between text-xs font-semibold text-ink-soft">
                                <span x-text="`Question ${step + 1} of ${total()}`"></span>
                                <span x-text="`${percent()}%`" class="tabular-nums"></span>
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-mist" role="progressbar" aria-label="Progress" :aria-valuenow="percent()" aria-valuemin="0" aria-valuemax="100">
                                <div class="h-full rounded-full bg-brand transition-[width] duration-500 ease-out-expo" :style="`width: ${percent()}%`"></div>
                            </div>
                            <p x-show="hint" x-text="hint" x-cloak class="mt-2 text-xs font-semibold text-brand" role="alert"></p>
                        </div>
                        <button type="button" x-show="step > 0" @click="back()" aria-label="Previous question"
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-line text-ink transition hover:border-ink/40 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-ink/10">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m12 5-5 5 5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="flex shrink-0 flex-col items-end">
                            <button type="button" @click="next()" class="btn btn-primary">Next</button>
                            <span class="mt-1 hidden text-[11px] text-muted sm:block">or press Enter</span>
                        </div>
                    </div>

                    {{-- Contact step --}}
                    <div x-show="step === total() && ! done" x-cloak class="finder-step-fwd min-h-0 flex-1 overflow-y-auto overscroll-contain">
                        <div class="grid sm:grid-cols-2">
                            <div class="flex flex-col justify-center gap-4 border-b border-line bg-mist px-5 py-6 sm:border-b-0 sm:border-r sm:px-8 sm:py-10">
                                <p class="text-sm font-semibold text-ink-soft">Great, that’s it. You’re almost done.</p>
                                <div class="h-1.5 overflow-hidden rounded-full bg-white">
                                    <div class="h-full rounded-full bg-brand transition-[width] duration-500 ease-out-expo" :style="`width: ${percent()}%`"></div>
                                </div>
                                <h2 :id="step === total() ? 'finder-title' : null" class="text-card-title font-bold leading-tight tracking-tight text-ink">Where should our advisor reach you?</h2>
                                <p class="text-[15px] leading-relaxed text-ink-soft">We’ll call you with new projects that match what you told us.</p>
                                <button type="button" @click="back()" class="hit-area self-start text-sm font-semibold text-ink underline decoration-brand decoration-2 underline-offset-4 hover:text-brand">Change my answers</button>
                            </div>
                            <form method="POST" action="{{ route('finder.store') }}" @submit.prevent="send($el)" novalidate class="px-5 py-6 sm:px-8 sm:py-10">
                                @csrf
                                <div class="absolute -left-[9999px]" aria-hidden="true">
                                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                                </div>
                                <template x-for="q in questions" :key="q.id">
                                    <template x-for="label in picks[q.id]" :key="label">
                                        <input type="hidden" :name="`answers[${q.id}][]`" :value="label">
                                    </template>
                                </template>

                                <div class="space-y-4">
                                    <label class="block">
                                        <span class="text-sm font-semibold text-ink">Name</span>
                                        <input type="text" name="name" required autocomplete="name" class="{{ $field }}">
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-ink">Phone number</span>
                                        <input type="tel" name="phone" required autocomplete="tel" inputmode="tel" placeholder="0300 1234567" class="{{ $field }}">
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-ink">Email <span class="font-normal text-muted">(optional)</span></span>
                                        <input type="email" name="email" autocomplete="email" class="{{ $field }}">
                                    </label>
                                </div>
                                <p x-show="error" x-text="error" x-cloak class="mt-4 text-sm font-semibold text-brand" role="alert"></p>
                                <button type="submit" :disabled="sending" class="btn btn-primary mt-6 w-full disabled:cursor-wait disabled:opacity-75">
                                    <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                    <span x-text="sending ? 'Sending…' : 'Send my details'">Send my details</span>
                                </button>
                                <p class="mt-4 text-xs leading-relaxed text-muted">By sending, you agree to our <a href="{{ route('privacy') }}" class="font-semibold text-ink underline underline-offset-2 hover:text-brand">privacy policy</a>. We only use your details to help you find a property.</p>
                            </form>
                        </div>
                    </div>

                    {{-- Sent --}}
                    <div x-show="done" x-cloak role="status"
                        x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        class="flex flex-col items-center px-6 py-14 text-center sm:py-20">
                        <span class="grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <h2 class="text-section mt-6 font-bold tracking-tight text-ink">Thank you</h2>
                        <p class="mt-3 max-w-md text-lg leading-relaxed text-ink-soft">An advisor will call you soon with projects that match what you’re looking for.</p>
                        <button type="button" @click="close()" class="btn btn-outline mt-8">Back to the site</button>
                    </div>
                </div>
            </div>
    </div>
@endif
