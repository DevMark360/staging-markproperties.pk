{{-- The admin search pop-up, added once to every admin page; only for a signed-in admin (never on the login page) --}}
@if (filament()->auth()->check())
    @livewire(\App\Livewire\AdminSearch::class)
@endif
