{{-- First row of the sidebar menu: opens the search pop-up (also Ctrl/⌘ K) --}}
<button type="button" class="mp-sidebar-search group" x-data x-on:click="$dispatch('open-admin-search')">
    <x-filament::icon icon="heroicon-o-magnifying-glass" class="mp-sidebar-search-icon" />
    <span class="mp-sidebar-search-label">Search</span>
    <kbd class="mp-sidebar-search-kbd">{{ str_contains((string) request()->userAgent(), 'Mac OS') ? '⌘K' : 'Ctrl K' }}</kbd>
</button>
