<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Models\BlogPost;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('category')) // one query for all categories, not one per row (A5)
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('')
                    ->disk('site')
                    ->imageWidth(72)
                    ->imageHeight(48)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),
                TextColumn::make('title')
                    ->weight('medium')
                    ->wrap()
                    ->description(fn (BlogPost $record) => $record->category?->name)
                    ->searchable(['title', 'slug', 'excerpt'])
                    ->sortable(),
                TextColumn::make('state')
                    ->label('Status')
                    ->state(fn (BlogPost $record) => $record->stateLabel())
                    ->badge()
                    ->color(fn (string $state) => ['Published' => 'success', 'Scheduled' => 'info', 'Draft' => 'gray'][$state]),
                TextColumn::make('published_at')
                    ->label('Publish date')
                    ->date('j M Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('read_minutes')
                    ->label('Read time')
                    ->suffix(' min')
                    ->alignEnd()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('blog_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload(),
                SelectFilter::make('state')
                    ->label('Status')
                    ->options(['published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Draft'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'published' => $query->live(),
                        'scheduled' => $query->where('status', 'published')->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '>', now())),
                        'draft' => $query->where('status', 'draft'),
                        default => $query,
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => $records->each(fn (BlogPost $post) => $post->update([
                            'status' => 'published',
                            'published_at' => $post->published_at ?? now(),
                        ])))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('draft')
                        ->label('Move to drafts')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'draft']))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No blog posts yet')
            ->emptyStateDescription('Write a post. Published posts appear on the website blog.');
    }
}
