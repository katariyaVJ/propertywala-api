<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Real Estate & Bank Auctions';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                // STEP 1: Basic Information
                Forms\Components\Wizard\Step::make('Basic Details')
                    ->icon('heroicon-o-information-circle')
                    ->description('Property type, code & title')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('property_code')
                                ->label('Property Code (e.g. 032)')
                                ->unique(ignoreRecord: true)
                                ->placeholder('032')
                                ->maxLength(30)
                                ->helperText('Unique short code for instant search in app.')
                                ->required(),
                            Forms\Components\Select::make('user_id')
                                ->label('Assigned User / Owner')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->default(fn () => auth()->id())
                                ->required(),
                            Forms\Components\Select::make('posted_by_role')
                                ->label('Posted By')
                                ->options([
                                    'Owner' => 'Owner',
                                    'Agent' => 'Agent / Broker',
                                    'Builder' => 'Builder / Developer',
                                    'Banker' => 'Bank / Financial Inst.',
                                ])
                                ->default('Owner')
                                ->required(),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('asset_type')
                                ->label('Asset Type')
                                ->options([
                                    'Property' => 'Property (Real Estate)',
                                    'Vehicle' => 'Vehicle (Bank Seized)',
                                ])
                                ->default('Property')
                                ->live()
                                ->required(),
                            Forms\Components\Select::make('intent')
                                ->label('Intent / Purpose')
                                ->options([
                                    'Sell' => 'For Sale',
                                    'Rent' => 'For Rent',
                                    'Bank Auction' => 'Bank Auction (SARFAESI)',
                                ])
                                ->default('Sell')
                                ->live()
                                ->required(),
                        ]),

                        Forms\Components\Select::make('property_sub_type')
                            ->label('Category / Sub-Type')
                            ->options(fn (Get $get): array => $get('asset_type') === 'Vehicle' ? [
                                'Commercial Truck' => 'Commercial Truck',
                                'Car / SUV' => 'Car / SUV',
                                'Bus / Van' => 'Bus / Van',
                                'Construction / JCB' => 'Construction / JCB',
                                'Tractor / Agro' => 'Tractor / Agro',
                                'Two Wheeler' => 'Two Wheeler',
                                'Other Vehicle' => 'Other Vehicle',
                            ] : [
                                'Apartment / Flat' => 'Apartment / Flat',
                                'Row House / Tenement' => 'Row House / Tenement',
                                'Bungalow / Villa' => 'Bungalow / Villa',
                                'Commercial Shop' => 'Commercial Shop',
                                'Office Space' => 'Office Space',
                                'Industrial / Factory' => 'Industrial / Factory',
                                'Agricultural Land' => 'Agricultural Land',
                                'Residential Plot / NA' => 'Residential Plot / NA',
                                'Penthouse' => 'Penthouse',
                            ])
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Property Title')
                            ->placeholder('e.g. 3 BHK Ultra Luxury Apartment in Vesu')
                            ->required()
                            ->maxLength(220),

                        Forms\Components\Textarea::make('description')
                            ->label('Full Description')
                            ->placeholder('Describe the property, features, nearby landmarks, etc.')
                            ->rows(4),
                    ]),

                // STEP 2: Pricing & Location
                Forms\Components\Wizard\Step::make('Price & Location')
                    ->icon('heroicon-o-currency-rupee')
                    ->description('Pricing and address details')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('price')
                                ->label('Actual Price (Numeric)')
                                ->numeric()
                                ->prefix('₹')
                                ->placeholder('12500000')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state && is_numeric($state)) {
                                        if ($state >= 10000000) {
                                            $cr = round($state / 10000000, 2);
                                            $set('price_display', "₹ {$cr} Cr");
                                        } elseif ($state >= 100000) {
                                            $lacs = round($state / 100000, 2);
                                            $set('price_display', "₹ {$lacs} Lacs");
                                        } else {
                                            $set('price_display', "₹ " . number_format($state));
                                        }
                                    }
                                }),
                            Forms\Components\TextInput::make('price_display')
                                ->label('Display Price (Formatted)')
                                ->placeholder('e.g. ₹ 1.25 Cr')
                                ->required(),
                            Forms\Components\TextInput::make('price_per_sqft')
                                ->label('Price Per Sq.Ft')
                                ->placeholder('e.g. ₹ 6,500/sq.ft'),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('city')
                                ->label('City')
                                ->options(fn () => City::pluck('name', 'name')->toArray())
                                ->searchable()
                                ->default('Surat')
                                ->required(),
                            Forms\Components\TextInput::make('locality')
                                ->label('Locality / Area')
                                ->placeholder('e.g. Vesu, Adajan, Pal')
                                ->required(),
                            Forms\Components\TextInput::make('pincode')
                                ->label('Pincode')
                                ->placeholder('e.g. 395007'),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('society_or_yard')
                                ->label('Society / Building / Yard Name')
                                ->placeholder('e.g. Avadh Copper Stone'),
                            Forms\Components\TextInput::make('contact_phone')
                                ->label('Contact Phone')
                                ->tel()
                                ->placeholder('+91 98765 43210')
                                ->required(),
                        ]),

                        Forms\Components\Toggle::make('is_negotiable')
                            ->label('Price is Negotiable')
                            ->default(true),
                    ]),

                // STEP 3: Specifications
                Forms\Components\Wizard\Step::make('Specifications')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->description('Dimensions, features and amenities')
                    ->schema([
                        // Property-specific fields
                        Forms\Components\Section::make('Real Estate Specs')
                            ->visible(fn (Get $get): bool => $get('asset_type') !== 'Vehicle')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('bhk')
                                        ->label('Configuration (BHK)')
                                        ->options([
                                            '1 RK' => '1 RK',
                                            '1 BHK' => '1 BHK',
                                            '2 BHK' => '2 BHK',
                                            '3 BHK' => '3 BHK',
                                            '4 BHK' => '4 BHK',
                                            '5+ BHK' => '5+ BHK',
                                            'Duplex' => 'Duplex',
                                            'Penthouse' => 'Penthouse',
                                            'Commercial' => 'Commercial Space',
                                            'Plot' => 'Open Plot',
                                        ]),
                                    Forms\Components\Select::make('bathrooms')
                                        ->label('Bathrooms')
                                        ->options([
                                            '1 Bathroom' => '1',
                                            '2 Bathrooms' => '2',
                                            '3 Bathrooms' => '3',
                                            '4+ Bathrooms' => '4+',
                                        ]),
                                    Forms\Components\Select::make('balconies')
                                        ->label('Balconies')
                                        ->options([
                                            'None' => '0',
                                            '1 Balcony' => '1',
                                            '2 Balconies' => '2',
                                            '3+ Balconies' => '3+',
                                        ]),
                                ]),
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('area_value')
                                        ->label('Area Size')
                                        ->numeric()
                                        ->placeholder('1850'),
                                    Forms\Components\Select::make('area_unit')
                                        ->label('Area Unit')
                                        ->options([
                                            'Sq.Ft' => 'Sq.Ft',
                                            'Sq.Yards' => 'Sq.Yards',
                                            'Vigha' => 'Vigha',
                                            'Acre' => 'Acre',
                                        ])
                                        ->default('Sq.Ft'),
                                    Forms\Components\Select::make('area_type')
                                        ->label('Area Type')
                                        ->options([
                                            'Super Built-up Area' => 'Super Built-up Area',
                                            'Carpet Area' => 'Carpet Area',
                                            'Plot Area' => 'Plot Area',
                                        ])
                                        ->default('Super Built-up Area'),
                                ]),
                                Forms\Components\Select::make('furnishing')
                                    ->label('Furnishing Status')
                                    ->options([
                                        'Unfurnished' => 'Unfurnished',
                                        'Semi-Furnished' => 'Semi-Furnished',
                                        'Fully Furnished' => 'Fully Furnished',
                                    ])
                                    ->default('Unfurnished'),
                                Forms\Components\TagsInput::make('amenities')
                                    ->label('Amenities')
                                    ->placeholder('Type and hit Enter (e.g. Lift, Parking, Gym, Pool)'),
                            ]),

                        // Vehicle-specific fields
                        Forms\Components\Section::make('Vehicle Specs')
                            ->visible(fn (Get $get): bool => $get('asset_type') === 'Vehicle')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('vehicle_category')
                                        ->label('Vehicle Category')
                                        ->placeholder('Commercial Truck, Tipper, Car'),
                                    Forms\Components\TextInput::make('vehicle_model')
                                        ->label('Model Name')
                                        ->placeholder('e.g. Tata Signa 2823.K'),
                                    Forms\Components\TextInput::make('vehicle_make_year')
                                        ->label('Manufacturing Year')
                                        ->numeric()
                                        ->placeholder('2022'),
                                    Forms\Components\TextInput::make('vehicle_km_driven')
                                        ->label('Kilometers Driven')
                                        ->numeric()
                                        ->placeholder('65000'),
                                    Forms\Components\Select::make('vehicle_fuel_type')
                                        ->label('Fuel Type')
                                        ->options([
                                            'Diesel' => 'Diesel',
                                            'Petrol' => 'Petrol',
                                            'CNG' => 'CNG',
                                            'Electric' => 'Electric',
                                        ]),
                                    Forms\Components\Select::make('vehicle_transmission')
                                        ->label('Transmission')
                                        ->options([
                                            'Manual' => 'Manual',
                                            'Automatic' => 'Automatic',
                                        ]),
                                    Forms\Components\TextInput::make('vehicle_reg_number')
                                        ->label('Registration (RTO Number)')
                                        ->placeholder('GJ-05-XX-1234'),
                                    Forms\Components\TextInput::make('vehicle_owner_type')
                                        ->label('Ownership')
                                        ->placeholder('First Owner (Hypothecated)'),
                                ]),
                                Forms\Components\TagsInput::make('vehicle_inspection_features')
                                    ->label('Inspection Checklist')
                                    ->placeholder('Add inspection detail (e.g. Tyres 80%, Clean Chassis)'),
                            ]),
                    ]),

                // STEP 4: Bank Auction (Optional)
                Forms\Components\Wizard\Step::make('Bank Auction')
                    ->icon('heroicon-o-scale')
                    ->description('SARFAESI / Distressed Asset Info')
                    ->schema([
                        Forms\Components\Toggle::make('is_bank_auction')
                            ->label('Mark as Bank Auction / Seized Asset')
                            ->default(false)
                            ->live(),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('bank_name')
                                ->label('Bank Name')
                                ->placeholder('e.g. State Bank of India')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('borrower_name')
                                ->label('Borrower / NPA Account')
                                ->placeholder('Borrower / Company Name')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('reserve_price')
                                ->label('Reserve Price (₹)')
                                ->numeric()
                                ->prefix('₹')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('emd_amount')
                                ->label('EMD Amount (₹)')
                                ->numeric()
                                ->prefix('₹')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('auction_date')
                                ->label('Auction Date (Text Display)')
                                ->placeholder('e.g. 25 Nov 2026 11:00 AM')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\DateTimePicker::make('auction_timestamp')
                                ->label('Auction Exact Date/Time')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('bank_branch_or_yard')
                                ->label('Branch / Vehicle Yard')
                                ->placeholder('e.g. SARB Branch, Ring Road, Surat')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('notice_pdf_url')
                                ->label('Auction Notice PDF URL')
                                ->url()
                                ->placeholder('https://...')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\Select::make('auction_status')
                                ->label('Auction Status')
                                ->options([
                                    'Upcoming' => 'Upcoming',
                                    'Live' => 'Live Now',
                                    'Concluded' => 'Concluded / Closed',
                                ])
                                ->default('Upcoming')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                        ]),
                    ]),

                // STEP 5: Photos & Status
                Forms\Components\Wizard\Step::make('Photos & Publishing')
                    ->icon('heroicon-o-photo')
                    ->description('Upload pictures & set visibility')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->label('Property Photos')
                            ->relationship('images')
                            ->schema([
                                Forms\Components\FileUpload::make('image_url')
                                    ->label('Upload Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('properties')
                                    ->visibility('public')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('label')
                                    ->label('Photo Tag')
                                    ->default('Photo')
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('is_cover')
                                    ->label('Main Cover')
                                    ->default(false)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->collapsible()
                            ->reorderable('sort_order'),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'Active' => 'Active (Live on App)',
                                    'Under Review' => 'Under Review',
                                    'Sold / Rented' => 'Sold / Rented',
                                    'Rejected' => 'Rejected',
                                ])
                                ->default('Active')
                                ->required(),
                            Forms\Components\Toggle::make('is_verified')
                                ->label('Verified Badge')
                                ->default(true),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured (Top of Feed)')
                                ->default(false),
                        ]),

                        Forms\Components\Textarea::make('admin_review_note')
                            ->label('Internal Admin Notes')
                            ->placeholder('Any internal notes about this listing...')
                            ->columnSpanFull(),
                    ]),
            ])
            ->skippable()
            ->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('coverImage.image_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl('https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=120&q=60'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Property / Code')
                    ->description(fn (Property $record): string => 
                        '#' . ($record->property_code ?? '---') . ' • ' . $record->property_sub_type . ' • ' . $record->intent
                    )
                    ->searchable(['title', 'property_code', 'property_sub_type', 'locality'])
                    ->wrap()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('locality')
                    ->label('Location')
                    ->description(fn (Property $record): string => $record->city)
                    ->searchable(['locality', 'city']),
                Tables\Columns\TextColumn::make('price_display')
                    ->label('Price')
                    ->description(fn (Property $record): ?string => $record->price_per_sqft)
                    ->sortable('price'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Under Review' => 'warning',
                        'Sold / Rented' => 'gray',
                        'Rejected' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\IconColumn::make('is_bank_auction')
                    ->label('Auction')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Under Review' => 'Under Review',
                        'Sold / Rented' => 'Sold / Rented',
                        'Rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('asset_type')
                    ->options([
                        'Property' => 'Property',
                        'Vehicle' => 'Vehicle',
                    ]),
                Tables\Filters\TernaryFilter::make('is_bank_auction')
                    ->label('Bank Auction'),
                Tables\Filters\SelectFilter::make('city')
                    ->options(fn () => City::pluck('name', 'name')->toArray()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Approve & Publish (Active)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Property $record) {
                            $record->update([
                                'status' => 'Active',
                                'is_verified' => true,
                            ]);
                            Notification::make()
                                ->title('Property Activated')
                                ->body("Property #{$record->property_code} is now Live & Active on the App.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Property $record) => $record->status !== 'Active'),

                    Tables\Actions\Action::make('deactivate')
                        ->label('Deactivate (Inactive)')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Property $record) {
                            $record->update([
                                'status' => 'Under Review',
                            ]);
                            Notification::make()
                                ->title('Property Inactivated')
                                ->body("Property #{$record->property_code} has been marked Inactive.")
                                ->warning()
                                ->send();
                        })
                        ->visible(fn (Property $record) => $record->status === 'Active'),

                    Tables\Actions\Action::make('assignCode')
                        ->label('Set Code')
                        ->icon('heroicon-o-identification')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('property_code')
                                ->label('Assign 3-digit Code (e.g. 032)')
                                ->required()
                                ->default(fn (Property $record) => $record->property_code),
                        ])
                        ->action(function (Property $record, array $data) {
                            $record->update(['property_code' => $data['property_code']]);
                            Notification::make()
                                ->title('Property Code Updated')
                                ->body("Code {$data['property_code']} assigned.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
