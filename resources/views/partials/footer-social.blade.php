{{-- Round social icons in one row. $class places/sizes the row; $size is the circle size; $tone: dark (footer) or light (phone menu). --}}
@php
    $icons = [
        'Facebook' => '<path fill="currentColor" d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.5-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.3H7.9v3h2.6V21h3Z"/>',
        'Instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3.8" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor"/>',
        'LinkedIn' => '<path fill="currentColor" d="M6.9 8.6H3.6V20h3.3V8.6ZM5.3 3.5a1.9 1.9 0 1 0 0 3.8 1.9 1.9 0 0 0 0-3.8ZM20.5 13.6c0-3.1-1.6-5.2-4.4-5.2-1.6 0-2.6.9-3.1 1.7V8.6H9.8V20h3.3v-5.9c0-1.5.6-2.6 2-2.6s1.9 1 1.9 2.6V20h3.4l.1-6.4Z"/>',
        'YouTube' => '<path fill="currentColor" d="M21.6 7.2a2.6 2.6 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4a2.6 2.6 0 0 0-1.8 1.8C2 8.8 2 12 2 12s0 3.2.4 4.8a2.6 2.6 0 0 0 1.8 1.8c1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4a2.6 2.6 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8ZM10 15V9l5.2 3L10 15Z"/>',
    ];
@endphp
<ul class="{{ $class }}">
    @foreach (config('site.social') as $network => $url)
        <li>
            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $network }}" title="{{ $network }}"
                @class(['grid place-items-center rounded-full border transition-colors duration-300 hover:border-brand hover:bg-brand hover:text-white', $size,
                    'border-white/15 text-white/85' => ($tone ?? 'dark') === 'dark',
                    'border-ink/25 text-ink-soft' => ($tone ?? 'dark') === 'light'])>
                @isset($icons[$network])
                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$network] !!}</svg>
                @else
                    <span class="text-sm font-semibold" aria-hidden="true">{{ mb_substr($network, 0, 1) }}</span>
                @endisset
            </a>
        </li>
    @endforeach
</ul>
