<?php

namespace App\Filament\Resources\LuxuryCollections\Schemas;

use App\Models\Project;
use App\Rules\ReadableImage;
use App\Support\ImageResizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LuxuryCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Section::make()->columnSpan(2)->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true)
                        ->placeholder('e.g. Waterfront Living'),
                    TextInput::make('slug')
                        ->label('Web address')
                        ->prefix('/luxury?collection=')
                        ->maxLength(80)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->placeholder('Filled from the name')
                        ->helperText('Leave empty to fill it from the name.'),
                    TextInput::make('tagline')
                        ->maxLength(255)
                        ->helperText('One short line under the name.'),
                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(500),
                ]),
                Section::make('Cover')->columnSpan(1)->schema([
                    FileUpload::make('cover')
                        ->hiddenLabel()
                        ->image()
                        ->acceptedFileTypes(ReadableImage::TYPES) // JPG, PNG or WebP only; checked before saving (C8)
                        ->rule(new ReadableImage)
                        ->disk('site')
                        ->visibility('public')
                        ->maxSize(15 * 1024)
                        ->imagePreviewHeight('200')
                        ->required()
                        ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file, Get $get) => ImageResizer::store(
                            $file,
                            'uploads/luxe',
                            Str::slug($get('name') ?: 'collection').'-'.Str::lower(Str::random(6)),
                        )['card'])
                        ->helperText('Used for the collection tile. Saved as WebP at 800px and 1600px wide.'),
                ]),
                Section::make('Projects')
                    ->description('Drag to set the order they appear in. Only published projects can be added.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->simple(
                                Select::make('project_id')
                                    ->options(fn () => Project::where('is_published', true)->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            )
                            ->addActionLabel('Add a project')
                            ->minItems(1)
                            ->defaultItems(1),
                    ]),
            ]),
        ]);
    }
}
