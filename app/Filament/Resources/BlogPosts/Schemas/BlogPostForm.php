<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Filament\Resources\BlogCategories\Schemas\BlogCategoryForm;
use App\Models\BlogPost;
use App\Rules\ReadableImage;
use App\Support\ImageResizer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                // Main column: the article
                Section::make()->columnSpan(2)->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')
                        ->label('Web address')
                        ->prefix('/blog/')
                        ->required()
                        ->maxLength(255)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->helperText('Filled from the title. Changing it after publishing breaks old links.'),
                    Textarea::make('excerpt')
                        ->rows(3)
                        ->maxLength(300)
                        ->helperText('One or two sentences shown on the blog cards and in Google results.'),
                    RichEditor::make('body')
                        ->label('Article')
                        ->required()
                        ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3', 'blockquote'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                        ->helperText('Use “H2” for section headings — they become the article’s table of contents.'),
                ]),

                // Side column: publishing
                Section::make('Publishing')->columnSpan(1)->schema([
                    Select::make('status')
                        ->options(BlogPost::STATUSES)
                        ->default('draft')
                        ->required()
                        ->native(false),
                    DateTimePicker::make('published_at')
                        ->label('Publish date')
                        ->default(now())
                        ->required(fn (Get $get) => $get('status') === 'published')
                        ->seconds(false)
                        ->helperText('A future date keeps a published post hidden until then.'),
                    Select::make('blog_category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        // Add a new category without leaving the post
                        ->createOptionForm(BlogCategoryForm::fields())
                        ->createOptionModalHeading('Add a category'),
                    FileUpload::make('cover_image')
                        ->label('Cover image')
                        ->image()
                        ->acceptedFileTypes(ReadableImage::TYPES) // JPG, PNG or WebP only; checked before saving (C8)
                        ->rule(new ReadableImage)
                        ->disk('site')
                        ->visibility('public')
                        ->maxSize(15 * 1024)
                        ->imagePreviewHeight('140')
                        ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file, Get $get) => ImageResizer::store(
                            $file,
                            'uploads/blog',
                            ($get('slug') ?: 'post').'-'.Str::lower(Str::random(6)),
                        )['card'])
                        ->helperText('Saved as WebP at 800px and 1600px wide.'),
                    TextInput::make('read_minutes')
                        ->label('Read time')
                        ->suffix('min')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Worked out on save')
                        ->helperText('Calculated from the article length (about 200 words a minute).'),
                ]),
            ]),
        ]);
    }
}
