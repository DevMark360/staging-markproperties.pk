{{-- Admin search pop-up. Opened by the sidebar "Search" item (open-admin-search event) or Ctrl/⌘ K; Esc or a click outside closes it. --}}
<div
    x-data="{
        open: false,
        show() { this.open = true; this.$nextTick(() => this.$refs.input.focus()) },
        hide() { this.open = false },
        // Arrow keys move through the result links
        move(step) {
            const links = [...this.$root.querySelectorAll('.mp-search-result')]
            if (! links.length) return
            const at = links.indexOf(document.activeElement)
            links[(at + step + links.length) % links.length].focus()
        },
    }"
    x-on:open-admin-search.window="show()"
    x-on:keydown.window.ctrl.k.prevent="open ? hide() : show()"
    x-on:keydown.window.meta.k.prevent="open ? hide() : show()"
    x-on:keydown.escape.window="hide()"
>
    <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="mp-search-backdrop" x-on:click.self="hide()">
        <div class="mp-search-panel" role="dialog" aria-modal="true" aria-label="Search the admin"
            x-show="open" x-transition.scale.95.origin.top.duration.150ms
            x-on:keydown.down.prevent="move(1)" x-on:keydown.up.prevent="move(-1)">
            <div class="mp-search-bar">
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="mp-search-bar-icon" />
                <input x-ref="input" type="search" wire:model.live.debounce.250ms="query" class="mp-search-input"
                    placeholder="Search projects, leads, blog posts or lists…" autocomplete="off" spellcheck="false">
                <kbd class="mp-search-kbd" x-on:click="hide()">ESC</kbd>
                <button type="button" class="mp-search-close" x-on:click="hide()" aria-label="Close search">
                    <x-filament::icon icon="heroicon-o-x-mark" class="size-5" />
                </button>
            </div>

            <div class="mp-search-body">
                @if ($results === null)
                    <div class="mp-search-empty">
                        <svg class="mp-search-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"/></svg>
                        <p>Type a name to search</p>
                    </div>
                @elseif ($results->isEmpty())
                    <div class="mp-search-empty">
                        <p>Nothing matches “{{ trim($query) }}”</p>
                    </div>
                @else
                    <div class="mp-search-results" wire:loading.class="opacity-60">
                        @foreach ($results as $group => $items)
                            <section>
                                <h3 class="mp-search-group">{{ \Illuminate\Support\Str::ucfirst($group) }}</h3>
                                <ul>
                                    @foreach ($items as $item)
                                        <li>
                                            <a href="{{ $item->url }}" class="mp-search-result">
                                                <span class="mp-search-result-title">{{ $item->title }}</span>
                                                @if ($item->details)
                                                    <span class="mp-search-result-details">
                                                        {{ collect($item->details)->map(fn ($value, $label) => is_string($label) ? "{$label}: {$value}" : $value)->implode(', ') }}
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
