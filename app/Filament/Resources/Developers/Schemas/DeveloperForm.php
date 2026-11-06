<?php

namespace App\Filament\Resources\Developers\Schemas;

use App\Rules\ReadableImage;
use App\Support\LogoProcessor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DeveloperForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Developer')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')
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
                    ->prefix('/developers/')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->helperText('Filled from the name. Changing it later breaks old links; its projects move with it.'),
                Toggle::make('is_partner')
                    ->label('Mark Properties partner')
                    ->helperText('Partners are listed first and marked on the website.')
                    ->inline(false),
                FileUpload::make('logo')
                    ->image()
                    ->acceptedFileTypes(ReadableImage::TYPES) // JPG, PNG or WebP only; checked before saving (C8)
                    ->rule(new ReadableImage)
                    ->disk('site')
                    ->visibility('public')
                    ->maxSize(5 * 1024)
                    ->imagePreviewHeight('120')
                    // One upload: saves the colour logo and makes the grey copy shown on the site automatically
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file, Get $get) => LogoProcessor::store($file, $get('slug') ?: 'developer')['logo'])
                    ->helperText('PNG, JPG or WebP. The grey version shown on the website is made automatically from this logo.')
                    ->columnSpanFull(),
            ]),
            Section::make('About')
                ->description('Shown on the developer’s page. Leave empty to use the automatic text built from their projects.')
                ->columnSpanFull()
                ->schema([
                    RichEditor::make('about')
                        ->hiddenLabel()
                        ->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                ]),
        ]);
    }
}
