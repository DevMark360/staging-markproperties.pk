<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV downloads for lead tables, written directly (no queue needed).
 *
 * $columns maps a heading to a callback that reads the value from a record:
 * ['Name' => fn ($r) => $r->name, ...]
 */
class CsvExport
{
    /** Toolbar button: exports every row matching the current search, filters and sort. */
    public static function action(string $filename, array $columns): Action
    {
        return Action::make('exportCsv')
            ->label('Export CSV')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->action(fn (HasTable $livewire) => self::download(
                $filename,
                $columns,
                $livewire->getFilteredSortedTableQuery()->lazy(500),
            ));
    }

    /** Bulk action: exports only the selected rows. */
    public static function bulkAction(string $filename, array $columns): BulkAction
    {
        return BulkAction::make('exportSelectedCsv')
            ->label('Export selected (CSV)')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => self::download($filename, $columns, $records));
    }

    /**
     * A cell that Excel and Google Sheets show as text, never run as a formula (R1). Visitors type these values into
     * the site's forms, so one starting with = + - @ (or a tab / line break) gets a leading apostrophe.
     */
    public static function cell(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r\n]/', $value) ? "'".$value : $value;
    }

    private static function download(string $filename, array $columns, iterable $records): StreamedResponse
    {
        $name = Str::slug($filename).'-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($columns, $records) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Urdu/special characters correctly
            fputcsv($out, array_keys($columns));
            foreach ($records as $record) {
                fputcsv($out, array_map(fn (callable $value) => self::cell($value($record)), $columns));
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
