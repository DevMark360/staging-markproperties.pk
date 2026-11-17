<?php

namespace App\Filament\Resources\FinderQuestions\Schemas;

use App\Models\FinderQuestion;
use App\Rules\ReadableImage;
use App\Support\ImageResizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FinderQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')
                    ->label('Question')
                    ->required()
                    ->maxLength(160)
                    ->placeholder('e.g. Which type of property are you looking for?')
                    ->columnSpanFull(),
                TextInput::make('hint')
                    ->label('Note under the question')
                    ->maxLength(200)
                    ->placeholder('e.g. You can choose more than one.')
                    ->helperText('Optional.')
                    ->columnSpanFull(),
                Select::make('source')
                    ->label('Options')
                    ->options(FinderQuestion::SOURCES)
                    ->default('custom')
                    ->required()
                    ->live()
                    ->helperText(fn (Get $get) => $get('source') === 'custom'
                        ? 'Add the options below.'
                        : 'Taken from the admin list, showing only entries with published projects. Add photos for them below.'),
                Toggle::make('multiple')
                    ->label('Visitors can pick more than one')
                    ->inline(false),
                Toggle::make('is_active')
                    ->label('Ask this question')
                    ->default(true)
                    ->inline(false)
                    ->helperText('Switch off to skip it without deleting it.'),
            ]),
            // Own options: a label and a photo each. A list (types, cities, areas, stages): pick an entry and give it a photo;
            // entries left out still show, with their own photo (areas) or a plain placeholder.
            Section::make(fn (Get $get) => $get('source') === 'custom' ? 'Options' : 'Photos')
                ->description(fn (Get $get) => $get('source') === 'custom'
                    ? 'Add photos to show the options as photo cards (an option without one gets a plain placeholder); with no photos at all they show as simple buttons.'
                    : 'Optional. Give any entry of the list a photo to show the options as photo cards. Areas already use their own photo unless you add one here.')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('options')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('label')
                                ->label('Option')
                                ->required()
                                ->maxLength(120)
                                ->visible(fn (Get $get) => $get('../../source') === 'custom'),
                            Select::make('key')
                                ->label(fn (Get $get) => FinderQuestion::SOURCES[$get('../../source')] ?? 'Entry')
                                ->options(fn (Get $get) => FinderQuestion::listOptions($get('../../source')))
                                ->searchable()
                                ->required()
                                ->distinct()
                                ->visible(fn (Get $get) => $get('../../source') !== 'custom'),
                            FileUpload::make('image')
                                ->label('Photo')
                                ->image()
                                ->acceptedFileTypes(ReadableImage::TYPES)
                                ->rule(new ReadableImage)
                                ->disk('site')
                                ->visibility('public')
                                ->maxSize(15 * 1024)
                                ->imagePreviewHeight('120')
                                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file, Get $get) => ImageResizer::store(
                                    $file,
                                    'uploads/finder',
                                    Str::slug($get('label') ?: $get('key') ?: 'option').'-'.Str::lower(Str::random(6)),
                                    ['card'],
                                )['card'])
                                ->helperText('Optional. Saved as WebP at 800px wide.'),
                        ])
                        ->columns(2)
                        ->minItems(fn (Get $get) => $get('source') === 'custom' ? 2 : 0)
                        ->defaultItems(fn (Get $get) => $get('source') === 'custom' ? 2 : 0)
                        ->reorderable(fn (Get $get) => $get('source') === 'custom') // list entries keep the list's order
                        ->addActionLabel(fn (Get $get) => $get('source') === 'custom' ? 'Add an option' : 'Add a photo')
                        ->itemLabel(fn (array $state, Get $get) => $state['label']
                            ?? (isset($state['key']) ? (FinderQuestion::listOptions($get('source'))[$state['key']] ?? null) : null))
                        ->required(fn (Get $get) => $get('source') === 'custom'),
                ]),
        ]);
    }
}
