<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Resources\Areas\Schemas\AreaForm;
use App\Filament\Support\FeatureList;
use App\Models\Area;
use App\Models\City;
use App\Models\Developer;
use App\Models\Project;
use App\Models\PropertyType;
use App\Models\Stage;
use App\Rules\ReadableImage;
use App\Support\GoogleMapsLocation;
use App\Support\ImageResizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Project')
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    self::basics(),
                    self::content(),
                    self::images(),
                    self::homePage(),
                    self::location(),
                ]),
        ]);
    }

    private static function basics(): Tab
    {
        return Tab::make('Basics')->icon('heroicon-o-building-office-2')->columns(2)->schema([
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
                ->prefix('/projects/')
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->helperText('Filled from the name. Changing it later breaks old links to this project.'),
            Select::make('developer_slug')
                ->label('Builder')
                ->options(fn () => Developer::options())
                ->searchable()
                ->required(),
            Select::make('stage_slug')
                ->label('Stage')
                ->options(fn () => Stage::options())
                ->placeholder('Not set'),
            Select::make('city_slug')
                ->label('City')
                ->options(fn () => City::options())
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('area_slug', null)),
            Select::make('area_slug')
                ->label('Area')
                ->options(fn (Get $get) => Area::optionsFor($get('city_slug')))
                ->searchable()
                ->required()
                ->helperText('Missing? Add it with +.')
                // Add a new area without leaving the project
                ->createOptionForm(fn (Get $get) => AreaForm::fields($get('city_slug')))
                ->createOptionUsing(fn (array $data) => Area::create($data)->slug)
                ->createOptionModalHeading('Add an area'),
            Select::make('type_slug')
                ->label('Property type')
                ->options(fn () => PropertyType::options()),
            Toggle::make('is_published')
                ->label('Published')
                ->helperText('Hidden projects stay in the admin but are not shown on the website.')
                ->default(true)
                ->inline(false),
            Textarea::make('address')
                ->label('Full address')
                ->rows(2)
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }

    private static function content(): Tab
    {
        return Tab::make('Content')->icon('heroicon-o-document-text')->schema([
            RichEditor::make('description')
                ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                ->helperText('No prices, payment plans or unit inventories — they are never shown on the website.'),
            Section::make('Highlights')
                ->description('The highlighted card on the project page.')
                ->columns(2)
                ->schema([
                    TextInput::make('highlights.title')->label('Title')->maxLength(255),
                    TextInput::make('highlights.subtitle')->label('Subtitle')->maxLength(255),
                    Repeater::make('highlights.points')
                        ->label('Points')
                        ->simple(TextInput::make('point')->required()->maxLength(300))
                        ->reorderable()
                        ->addActionLabel('Add point')
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),
            Section::make('Amenities & utilities')
                ->description('Pick from the Amenities and Utilities lists. Missing one? Add it with +.')
                ->columns(2)
                ->schema([
                    self::features('amenityItems', 'Amenities', 'amenity'),
                    self::features('utilityItems', 'Utilities', 'utility'),
                ]),
        ]);
    }

    /** A multi-select over the Amenities or Utilities list, with "add one" in place. */
    private static function features(string $relation, string $label, string $kind): Select
    {
        return Select::make($relation)
            ->label($label)
            ->relationship($relation, 'name', fn ($query) => $query->reorder('name'))
            ->multiple()
            ->preload()
            ->searchable()
            ->createOptionForm(FeatureList::fields($kind))
            ->createOptionModalHeading('Add '.($kind === 'amenity' ? 'an amenity' : 'a utility'));
    }

    private static function images(): Tab
    {
        return Tab::make('Images')->icon('heroicon-o-photo')->schema([
            self::imageUpload('image', 'card')
                ->label('Main image')
                ->helperText('Shown on project cards and as the first gallery photo. Saved as WebP at 800px and 1600px wide.'),
            self::imageUpload('gallery', 'card')
                ->label('Gallery')
                ->multiple()
                ->reorderable()
                ->appendFiles()
                ->panelLayout('grid')
                ->maxFiles(30)
                ->helperText('Drag to reorder. Up to 30 photos, 10 MB each; each is saved as WebP at 800px and 1600px wide.'),
        ]);
    }

    private static function homePage(): Tab
    {
        return Tab::make('Home page')->icon('heroicon-o-home')->schema([
            self::sectionSwitch('home', 'show_on_home', 'Show in the home page slider', 'The featured slider at the top of the home page.'),
            self::imageUpload('hero_image', 'hero', ['hero'])
                ->label('Slider photo')
                // Smaller photos look blurry stretched across a desktop screen
                ->rules(['dimensions:min_width='.config('images.hero_min_width')])
                ->validationMessages(['dimensions' => 'The slider photo must be at least '.config('images.hero_min_width').'px wide, or it looks blurry on desktop screens.'])
                ->helperText('A wide landscape photo, at least '.config('images.hero_min_width').'px wide (saved at up to 2400px). If empty, the main image is used.')
                ->visible(fn (Get $get) => (bool) $get('show_on_home')),
            self::sectionSwitch('trending', 'is_trending', 'Show in Trending', 'The “Trending projects across Pakistan” section.'),
        ]);
    }

    /**
     * A section switch, plus a "replace which project?" picker that appears when the section is already full.
     * The picker is not saved on the project; the page passes it to Project::syncSections() after saving.
     */
    private static function sectionSwitch(string $section, string $flag, string $label, string $help): Section
    {
        $limit = Project::sectionLimit($section);
        $others = fn (?Project $record) => Project::inSection($section)->when($record, fn ($q) => $q->whereKeyNot($record->getKey()));
        $needsReplacement = fn (Get $get, ?Project $record) => $get($flag)
            && ! ($record?->getOriginal($flag))
            && $others($record)->count() >= $limit;

        return Section::make($label)->description($help)->compact()->schema([
            Toggle::make($flag)
                ->label($label)
                ->live()
                ->helperText(fn (?Project $record) => 'Up to '.$limit.' projects. Currently '.$others($record)->count().' others. Set the order in Content → '.Project::SECTIONS[$section]['label'].'.'),
            Select::make('replace_'.$section)
                ->label(Project::SECTIONS[$section]['label'].' already has '.$limit.' projects. Choose one to remove')
                ->options(fn (?Project $record) => $others($record)->pluck('name', 'id')->all())
                ->required(fn (Get $get, ?Project $record) => $needsReplacement($get, $record))
                ->visible(fn (Get $get, ?Project $record) => $needsReplacement($get, $record))
                ->dehydrated(false),
        ]);
    }

    private static function location(): Tab
    {
        return Tab::make('Location & brochure')->icon('heroicon-o-map-pin')->columns(2)->schema([
            // Google's "Embed a map" code: saved (only its Google address) and shown as the map on the project page, and
            // its pin fills Latitude and Longitude, which place the project on the projects map. A plain Google Maps
            // link only fills the coordinates (the page then draws its usual map from them).
            Textarea::make('map_embed')
                ->label('Google Maps embed code')
                ->rows(3)
                ->placeholder('<iframe src="https://www.google.com/maps/embed?pb=..." ...></iframe>')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Set $set) {
                    if (blank($state)) {
                        return;
                    }
                    $point = GoogleMapsLocation::coordinates((string) $state);
                    if (! $point) {
                        Notification::make()->danger()
                            ->title('No location found in that code')
                            ->body('In Google Maps, open the project, click Share, then "Embed a map", copy the HTML and paste it here.')
                            ->send();

                        return;
                    }
                    $set('latitude', (string) $point[0]);
                    $set('longitude', (string) $point[1]);
                    $embed = GoogleMapsLocation::embedUrl((string) $state);
                    $set('map_embed', $embed);
                    Notification::make()->success()
                        ->title($embed ? 'Map and location set' : 'Location set')
                        ->body("Latitude {$point[0]}, longitude {$point[1]}.".($embed ? ' The project page will show this Google map.' : '').' Save the project to keep it.')
                        ->send();
                })
                // Only Google's own embed address is kept, never other HTML
                ->dehydrateStateUsing(fn ($state) => GoogleMapsLocation::embedUrl((string) $state))
                ->helperText('In Google Maps, open the project, click Share, then "Embed a map" and copy the HTML. Paste it here and click outside the box: the project page shows this map, and the exact pin fills Latitude and Longitude below (used on the projects map). Empty: the page draws a map from the coordinates.')
                ->columnSpanFull(),
            // Plain text boxes (not number boxes): a number box silently refuses Google's "lat, lng" paste and keeps
            // its old value. Pasting the pair into Latitude fills both. Stored with all 15 decimal places.
            TextInput::make('latitude')
                ->inputMode('decimal')
                ->rules(['nullable', 'numeric', 'between:-90,90'])
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Set $set) {
                    if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', (string) $state, $pair)) {
                        $set('latitude', $pair[1]);
                        $set('longitude', $pair[2]);
                    }
                })
                ->dehydrateStateUsing(fn ($state) => filled($state) ? trim((string) $state) : null)
                ->helperText('In Google Maps, right-click the building, click the coordinates to copy them, and paste them here. Both numbers are filled in.'),
            TextInput::make('longitude')
                ->inputMode('decimal')
                ->rules(['nullable', 'numeric', 'between:-180,180'])
                ->dehydrateStateUsing(fn ($state) => filled($state) ? trim((string) $state) : null),
            FileUpload::make('brochure')
                ->label('Brochure (PDF)')
                ->disk('site')
                ->directory('uploads/brochures')
                ->visibility('public')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20 * 1024)
                ->downloadable()
                ->helperText('Buyers who request the brochure on the project page can download it. Up to 20 MB.')
                ->columnSpanFull(),
        ]);
    }

    /** An image field whose uploads go through ImageResizer (WebP, 800px + 1600px); stores the $keep size's path. */
    /** @param  list<string>|null  $sizes  sizes to make (null = the defaults); $keep is the one saved on the project */
    private static function imageUpload(string $field, string $keep, ?array $sizes = null): FileUpload
    {
        return FileUpload::make($field)
            ->image()
            ->acceptedFileTypes(ReadableImage::TYPES) // JPG, PNG or WebP only; checked before saving (C8)
            ->rule(new ReadableImage)
            ->disk('site')
            ->visibility('public')
            ->maxSize(10 * 1024) // 10 MB: plenty for a 12 MP phone photo; keeps big gallery saves quick (A4)
            ->imagePreviewHeight('160')
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file, Get $get) => ImageResizer::store(
                $file,
                'uploads/projects',
                ($get('slug') ?: 'project').'-'.Str::lower(Str::random(6)),
                $sizes,
            )[$keep]);
    }
}
