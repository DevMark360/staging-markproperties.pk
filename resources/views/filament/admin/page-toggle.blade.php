{{-- Show / hide the sidebar, beside the page's breadcrumbs (or title) on every admin page --}}
<button type="button" class="mp-page-toggle" x-data aria-controls="fi-main-sidebar"
    x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open($event.currentTarget)"
    x-bind:aria-expanded="$store.sidebar.isOpen"
    x-bind:aria-label="$store.sidebar.isOpen ? 'Hide menu' : 'Show menu'"
    x-bind:title="$store.sidebar.isOpen ? 'Hide menu' : 'Show menu'">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect width="18" height="18" x="3" y="3" rx="2" />
        <path d="M9 3v18" />
        <path x-show="$store.sidebar.isOpen" d="m16 15-3-3 3-3" />
        <path x-show="! $store.sidebar.isOpen" x-cloak d="m14 9 3 3-3 3" />
    </svg>
</button>
