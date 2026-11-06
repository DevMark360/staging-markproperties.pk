{{-- Sidebar top: the Mark Properties / AbadRaho.com logo. Clicking it opens a small menu with who is signed in and their profile. --}}
@php($user = filament()->auth()->user())

<div class="mp-brand-menu" x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:click.outside="open = false">
    <button type="button" class="mp-brand-btn" x-on:click="open = ! open" x-bind:aria-expanded="open" aria-haspopup="menu" aria-controls="mp-brand-dropdown">
        <img src="{{ asset('images/logo-dark.png') }}" alt="Mark Properties and AbadRaho.com" width="720" height="226" class="mp-brand-logo mp-brand-logo-light">
        <img src="{{ asset('images/logo-light.png') }}" alt="Mark Properties and AbadRaho.com" width="720" height="226" class="mp-brand-logo mp-brand-logo-dark">
        <x-filament::icon icon="heroicon-m-chevron-up-down" class="mp-brand-chevron" />
    </button>

    <div id="mp-brand-dropdown" class="mp-brand-dropdown" role="menu" x-cloak x-show="open" x-transition.origin.top.duration.120ms>
        @if ($user)
            <div class="mp-brand-user">
                <span class="mp-brand-user-name">{{ filament()->getUserName($user) }}</span>
                <span class="mp-brand-user-email">{{ $user->email }}</span>
            </div>
        @endif
        @if ($profileUrl = filament()->getProfileUrl())
            <a href="{{ $profileUrl }}" class="mp-brand-item" role="menuitem">
                <x-filament::icon icon="heroicon-o-user-circle" class="mp-brand-item-icon" />
                My profile
            </a>
        @endif
    </div>
</div>
