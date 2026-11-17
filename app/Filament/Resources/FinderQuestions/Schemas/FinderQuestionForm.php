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
                        : 'Taken from the admin list, showing only entries with published projects. Area photos are shown with them.'),
                Toggle::make('multiple')
                    ->label('Visitors can pick more than one')
                    ->inline(false),
                Toggle::make('is_active')
                    ->label('Ask this question')
                    ->default(true)
                    ->inline(false)
                    ->helperText('Switch off to skip it without deleting it.'),
            ]),
            Section::make('Options')
                ->description('Add a photo to every option to show them as photo cards; without photos they show as simple buttons.')
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('source') === 'custom')
                ->schema([
                    Repeater::make('options')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('label')
                                ->label('Option')
                                ->required()
                                ->maxLength(120),
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
                                    Str::slug($get('label') ?: 'option').'-'.Str::lower(Str::random(6)),
                                    ['card'],
                                )['card'])
                                ->helperText('Optional. Saved as WebP at 800px wide.'),
                        ])
                        ->columns(2)
                        ->minItems(2)
                        ->defaultItems(2)
                        ->reorderable()
                        ->addActionLabel('Add an option')
                        ->itemLabel(fn (array $state) => $state['label'] ?? null)
                        ->required(fn (Get $get) => $get('source') === 'custom'),
                ]),
        ]);
    }
}
