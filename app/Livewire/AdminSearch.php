<?php

namespace App\Livewire;

use Filament\GlobalSearch\Providers\DefaultGlobalSearchProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * The admin search pop-up (sidebar "Search", or Ctrl/⌘ K): searches every admin list that has a title column
 * (projects, leads, blog posts, developers, cities…) and links to the matching records, grouped by list.
 */
class AdminSearch extends Component
{
    private const MIN_LENGTH = 2;

    public string $query = '';

    /** @return Collection<string, iterable<\Filament\GlobalSearch\GlobalSearchResult>>|null  null until enough is typed */
    public function results(): ?Collection
    {
        // Leads and contacts are searchable, so a signed-out visitor never gets results (even by calling the component directly)
        if (! auth()->guard(filament()->getAuthGuard())->check()) {
            return null;
        }

        $query = trim($this->query);
        if (mb_strlen($query) < self::MIN_LENGTH) {
            return null;
        }

        return app(DefaultGlobalSearchProvider::class)->getResults($query)?->getCategories() ?? collect();
    }

    public function render(): View
    {
        return view('livewire.admin-search', ['results' => $this->results()]);
    }
}
