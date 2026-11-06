<?php

namespace App\Filament\Support;

use App\Models\Feature;
use App\Support\FeatureIcons;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

/** Form and table shared by the Amenities and Utilities lists. */
class FeatureList
{
    public static function form(Schema $schema, string $kind): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema(self::fields($kind)),
        ]);
    }

    /** Also used by "add one" on the project form. */
    public static function fields(string $kind): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(80)
                ->unique('features', 'name', ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->where('kind', $kind))
                ->live(onBlur: true)
                // Suggest an icon from the name while the admin has not picked one
                ->afterStateUpdated(function ($set, $get, ?string $state) {
                    if (! $get('icon') || $get('icon') === FeatureIcons::FALLBACK) {
                        $set('icon', FeatureIcons::guess((string) $state));
                    }
                }),
            Select::make('icon')
                ->options(FeatureIcons::options())
                ->allowHtml()
                ->searchable(false)
                ->native(false)
                ->default(FeatureIcons::FALLBACK)
                ->required()
                ->helperText('Shown beside the name on the project page.'),
        ];
    }

    public static function table(Table $table, string $model): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('projects'))
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginated([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([
                TextColumn::make('icon')
                    ->label('')
                    // HtmlString, so the SVG is not stripped like ordinary HTML
                    ->formatStateUsing(fn (?string $state) => new HtmlString(FeatureIcons::svg($state, ['width' => '22', 'height' => '22'])))
                    ->width('1%'),
                TextColumn::make('name')->weight('medium')->searchable()->sortable(),
                TextColumn::make('projects_count')->label('Projects')->sortable()->alignEnd(),
            ])
            ->recordActions([
                EditAction::make(),
                self::mergeAction($model),
                self::deleteAction(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Nothing here yet')
            ->emptyStateDescription('Add one, or run "php artisan catalogue:import" to copy the ones used on the website.');
    }

    public static function deleteAction(DeleteAction $action): DeleteAction
    {
        return $action->modalDescription(fn (Feature $record) => ($n = $record->projects()->count())
            ? 'It will be removed from '.$n.' '.str('project')->plural($n).'. To keep it on them under another name, use “Merge” instead.'
            : 'No project uses it.');
    }

    /** Moves the projects of a duplicate onto the item to keep, then deletes the duplicate. */
    public static function mergeAction(string $model): Action
    {
        return Action::make('merge')
            ->label('Merge')
            ->icon(Heroicon::OutlinedArrowsPointingIn)
            ->color('gray')
            ->modalHeading(fn (Feature $record) => 'Merge “'.$record->name.'” into…')
            ->modalDescription('Projects with this one get the one you pick instead; this one is then deleted.')
            ->modalSubmitActionLabel('Merge')
            ->schema(fn (Feature $record) => [
                Select::make('target')
                    ->label('Keep')
                    ->options($model::whereKeyNot($record->getKey())->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ])
            ->action(function (Feature $record, array $data) use ($model) {
                $target = $model::findOrFail($data['target']);
                $record->mergeInto($target);
                Notification::make()->title('Merged into “'.$target->name.'”')->success()->send();
            });
    }
}
