{{-- Sidebar footer: open the public site, and log out (your profile is in the logo menu at the top). Hidden with the sidebar. --}}
@php
    $links = [
        ['label' => 'View website', 'icon' => 'heroicon-o-arrow-top-right-on-square', 'url' => url('/'), 'external' => true],
    ];
@endphp

<div class="mp-sidebar-footer" x-show="$store.sidebar.isOpen">
    <ul class="mp-sidebar-footer-list">
        @foreach ($links as $link)
            @continue(blank($link['url']))
            <li>
                <a href="{{ $link['url'] }}" class="mp-sidebar-footer-item"
                    @if ($link['external']) target="_blank" rel="noopener" @endif>
                    <x-filament::icon :icon="$link['icon']" class="mp-sidebar-footer-icon" />
                    <span>{{ $link['label'] }}</span>
                </a>
            </li>
        @endforeach
        <li>
            <form method="post" action="{{ filament()->getLogoutUrl() }}">
                @csrf
                <button type="submit" class="mp-sidebar-footer-item">
                    <x-filament::icon icon="heroicon-o-arrow-left-start-on-rectangle" class="mp-sidebar-footer-icon" />
                    <span>Log out</span>
                </button>
            </form>
        </li>
    </ul>
</div>
