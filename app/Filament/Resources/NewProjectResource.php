<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewProjectResource\Pages;
use App\Models\NewProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewProjectResource extends Resource
{
    protected static ?string $model = NewProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Real Estate & Bank Auctions';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('new_projects.manage') ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('new_projects.manage') ?? false);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('new_projects.manage') ?? false);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('new_projects.manage') ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Project Identity & Developer')
                ->description('Official RERA registration and developer branding')
                ->icon('heroicon-o-building-office')
                ->schema([
                    Forms\Components\Grid::make(['default' => 1, 'md' => 2])->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Project Title')
                            ->placeholder('e.g. Avadh Copper Stone')
                            ->required()
                            ->maxLength(160),
                        Forms\Components\TextInput::make('developer_name')
                            ->label('Developer / Builder')
                            ->placeholder('e.g. Avadh Group')
                            ->required()
                            ->maxLength(140),
                        Forms\Components\TextInput::make('rera_number')
                            ->label('RERA Registration Number')
                            ->placeholder('PR/GJ/SURAT/...')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(80),
                        Forms\Components\TextInput::make('developer_stats')
                            ->label('Developer Track Record')
                            ->placeholder('e.g. 25+ Delivered Projects • 18 Yrs Experience')
                            ->maxLength(100),
                    ]),
                ]),

            Forms\Components\Section::make('Location & Geography')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    Forms\Components\Grid::make(['default' => 1, 'md' => 3])->schema([
                        Forms\Components\TextInput::make('locality')
                            ->label('Locality / Area')
                            ->placeholder('e.g. VIP Road, Vesu')
                            ->required()
                            ->maxLength(160),
                        Forms\Components\Select::make('city')
                            ->label('City')
                            ->options(fn () => \App\Models\City::pluck('name', 'name')->toArray())
                            ->default('Surat')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('state')
                            ->label('State')
                            ->options([
                                'Gujarat' => 'Gujarat',
                                'Maharashtra' => 'Maharashtra',
                                'Rajasthan' => 'Rajasthan',
                                'Madhya Pradesh' => 'Madhya Pradesh',
                                'Delhi NCR' => 'Delhi NCR',
                                'Karnataka' => 'Karnataka',
                                'Tamil Nadu' => 'Tamil Nadu',
                                'Uttar Pradesh' => 'Uttar Pradesh',
                                'Other' => 'Other State',
                            ])
                            ->default('Gujarat')
                            ->searchable(),
                    ]),
                ]),

            Forms\Components\Section::make('Pricing, Configuration & Timeline')
                ->icon('heroicon-o-currency-rupee')
                ->schema([
                    Forms\Components\Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])->schema([
                        Forms\Components\TextInput::make('price_range')
                            ->label('Price Range')
                            ->placeholder('e.g. ₹ 1.25 Cr - 2.80 Cr')
                            ->required(),
                        Forms\Components\TextInput::make('bhk_options')
                            ->label('BHK Configurations')
                            ->placeholder('e.g. 3, 4 & 5 BHK')
                            ->required(),
                        Forms\Components\TextInput::make('possession')
                            ->label('Possession Date')
                            ->placeholder('e.g. Dec 2027')
                            ->required(),
                        Forms\Components\TextInput::make('construction_stage')
                            ->label('Construction Stage')
                            ->placeholder('e.g. Under Construction (7th Slab)'),
                        Forms\Components\TextInput::make('total_units')
                            ->label('Total Inventory Units')
                            ->placeholder('e.g. 160 Units across 4 Towers'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Mark as Featured Project')
                            ->default(true),
                    ]),
                ]),

            Forms\Components\Section::make('Brochure, Amenities & Overview')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\TextInput::make('brochure_url')
                        ->label('Project Brochure URL (PDF)')
                        ->url()
                        ->placeholder('https://...')
                        ->maxLength(500),
                    Forms\Components\TagsInput::make('tags')
                        ->label('Highlights & Badges')
                        ->placeholder('Add highlight (e.g. RERA Approved, High ROI, River Facing)')
                        ->suggestions(['RERA Approved', 'Ultra Luxury', 'River Facing', 'Clubhouse', 'Prime Location']),
                    Forms\Components\TagsInput::make('amenities')
                        ->label('Project Amenities')
                        ->placeholder('Add amenity (e.g. Infinity Pool, Banquet, Gymnasium)')
                        ->suggestions(['Infinity Pool', 'Gymnasium', 'Landscaped Garden', 'Club House', 'EV Charging Station']),
                    Forms\Components\Textarea::make('description')
                        ->label('Project Description')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Project Units & Floor Configurations')
                ->description('Specify available unit types, areas, and estimated pricing')
                ->icon('heroicon-o-home')
                ->schema([
                    Forms\Components\Repeater::make('units')
                        ->relationship('units')
                        ->schema([
                            Forms\Components\TextInput::make('unit_type')
                                ->label('Unit Type')
                                ->placeholder('e.g. 3 BHK Luxury')
                                ->required(),
                            Forms\Components\TextInput::make('carpet_area_sqft')
                                ->label('Carpet Area (Sq.Ft)')
                                ->numeric()
                                ->placeholder('1850')
                                ->required(),
                            Forms\Components\TextInput::make('price_estimate')
                                ->label('Price Estimate')
                                ->placeholder('e.g. ₹ 1.25 Cr')
                                ->required(),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['unit_type'] ?? null),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project / Developer')
                    ->description(fn (NewProject $record): string => $record->developer_name . ' • RERA: ' . $record->rera_number)
                    ->searchable(['name', 'developer_name', 'rera_number'])
                    ->weight('bold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('locality')
                    ->label('Location')
                    ->description(fn (NewProject $record): string => ($record->city ?? '') . ($record->state ? ', ' . $record->state : ''))
                    ->searchable(['locality', 'city', 'state']),
                Tables\Columns\TextColumn::make('price_range')
                    ->label('Price Range')
                    ->description(fn (NewProject $record): ?string => $record->bhk_options),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->options(fn () => \App\Models\City::pluck('name', 'name')->toArray()),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('new_projects.manage') ?? false)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewProjects::route('/'),
            'create' => Pages\CreateNewProject::route('/create'),
            'edit' => Pages\EditNewProject::route('/{record}/edit'),
        ];
    }
}

