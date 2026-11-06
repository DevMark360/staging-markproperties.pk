<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/** "From date / To date" filter on created_at, shared by the lead tables. Two boxes side by side in the table header. */
class DateRangeFilter
{
    public static function make(): Filter
    {
        $picker = fn (string $name, string $label) => DatePicker::make($name)
            ->label($label)
            ->placeholder($label) // the label is hidden in the header, so the empty box says what it is
            ->native(false)
            ->displayFormat('j M Y')
            ->closeOnDateSelection();

        return Filter::make('date')
            ->schema([
                $picker('from', 'From date'),
                $picker('until', 'To date'),
            ])
            ->columns(2)
            ->columnSpan(2)
            ->query(fn (Builder $query, array $data) => $query
                ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)))
            ->indicateUsing(fn (array $data) => array_filter([
                ($data['from'] ?? null) ? 'From '.Carbon::parse($data['from'])->format('j M Y') : null,
                ($data['until'] ?? null) ? 'To '.Carbon::parse($data['until'])->format('j M Y') : null,
            ]));
    }
}
