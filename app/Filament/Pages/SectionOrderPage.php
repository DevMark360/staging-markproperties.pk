<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

/** Drag-and-drop order for one home page section (see Project::SECTIONS). */
abstract class SectionOrderPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    /** Key in Project::SECTIONS */
    abstract protected static function section(): string;

    public function getSubheading(): ?string
    {
        return 'Drag projects into the order shown on the home page. Up to '.Project::sectionLimit(static::section())
            .' projects — add more from a project’s “Home page” tab.';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    public function table(Table $table): Table
    {
        $s = Project::SECTIONS[static::section()];

        return $table
            ->query(Project::query()->where($s['flag'], true))
            ->defaultSort($s['order'])
            ->reorderable($s['order'])
            ->paginated(false)
            ->columns([
                TextColumn::make($s['order'])->label('#')->width('3rem'),
                ImageColumn::make('image')->label('')->disk('site')->imageWidth(64)->imageHeight(48)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),
                TextColumn::make('name')->weight('medium')
                    ->description(fn (Project $record) => $record->area.', '.$record->cityName()),
                TextColumn::make('developer_slug')->label('Builder')
                    ->formatStateUsing(fn (Project $record) => $record->developerName()),
                TextColumn::make('is_published')->label('Status')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Published' : 'Hidden — not shown')
                    ->badge()
                    ->color(fn (bool $state) => $state ? 'success' : 'warning'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (Project $record) => ProjectResource::getUrl('edit', ['record' => $record, 'tab' => 'home-page::data::tab'])),
                Action::make('remove')
                    ->label('Remove')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(fn (Project $record) => $record->name.' will no longer appear in this section. The project itself is not deleted.')
                    ->action(function (Project $record) use ($s) {
                        $record->forceFill([$s['flag'] => false, $s['order'] => null])->save();
                        Project::renumber(static::section()); // no gap left in the numbers (A8)
                        Notification::make()->title('Removed from '.$s['label'])->success()->send();
                    }),
            ])
            ->emptyStateHeading('No projects in this section')
            ->emptyStateDescription('Open a project, go to its “Home page” tab and switch this section on.');
    }
}
