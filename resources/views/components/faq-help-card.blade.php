{{-- "Still have questions?" box beside the home page FAQs --}}
<div {{ $attributes->class('relative overflow-hidden rounded-3xl bg-ink p-7 text-white') }}>
    <span class="absolute -right-14 -top-14 h-44 w-44 rounded-full bg-brand/30 blur-3xl" aria-hidden="true"></span>
    <p class="relative text-lg font-bold">Still have questions?</p>
    <p class="relative mt-2 text-sm leading-relaxed text-white/65">Our advisors can walk you through any project, its approvals and the booking process.</p>
    <div class="relative mt-6 flex flex-wrap gap-2">
        <a href="{{ route('contact', ['topic' => 'buying']) }}#message" class="hit-area btn btn-primary btn-sm">Send an enquiry</a>
        <a href="{{ route('faqs') }}" class="hit-area btn btn-outline-light btn-sm">All FAQs</a>
    </div>
</div>
