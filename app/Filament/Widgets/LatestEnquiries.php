<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProjectEnquiries\ProjectEnquiryResource;
use App\Models\ProjectEnquiry;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** The newest project enquiries, each opening its record. */
class LatestEnquiries extends TableWidget
{
    private const ROWS = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest project enquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => ProjectEnquiry::query()->latest()->limit(self::ROWS))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->weight('medium')
                    ->description(fn (ProjectEnquiry $record) => $record->phone),
                TextColumn::make('project_name')
                    ->label('Project'),
                TextColumn::make('interest')
                    ->label('Interested in')
                    ->formatStateUsing(fn (ProjectEnquiry $record) => $record->interestLabel())
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->dateTimeTooltip(),
            ])
            ->recordUrl(fn (ProjectEnquiry $record) => ProjectEnquiryResource::getUrl('edit', ['record' => $record]))
            ->headerActions([
                Action::make('all')
                    ->label('View all')
                    ->url(ProjectEnquiryResource::getUrl())
                    ->link(),
            ])
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('Enquiries sent from project pages appear here.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
