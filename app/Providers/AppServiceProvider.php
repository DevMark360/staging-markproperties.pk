<?php

namespace App\Providers;

use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureAdminTables();
    }

    /**
     * Admin lists show their filters in the table header (left of Export and Search) instead of behind a filter
     * button. Each filter applies as soon as it changes, and shows its name inside the empty box instead of a label
     * above it, so the filters fit the header rows.
     */
    private function configureAdminTables(): void
    {
        Table::configureUsing(fn (Table $table) => $table
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(['sm' => 2, 'lg' => 3, 'xl' => 4])
            ->deferFilters(false));

        SelectFilter::configureUsing(fn (SelectFilter $filter) => $filter->placeholder(fn (): string => $filter->getLabel()));

        // Yes/no filters set their own "-" after the line above runs, so they get theirs here
        TernaryFilter::configureUsing(fn (TernaryFilter $filter) => $filter->placeholder(fn (): string => $filter->getLabel().': all'));
    }
}
