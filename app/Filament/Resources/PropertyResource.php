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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isOwner()
            || (auth()->user()?->hasAdminPermission('properties.view') ?? false)
            || (auth()->user()?->hasAdminPermission('bank_auctions.manage') ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isOwner()
            || (auth()->user()?->hasAdminPermission('properties.create') ?? false)
            || (auth()->user()?->hasAdminPermission('bank_auctions.manage') ?? false);
    }

    public static function canEdit($record): bool
    {
        if (auth()->user()?->isOwner()) {
            return true;
        }

        if ($record?->is_bank_auction && (auth()->user()?->hasAdminPermission('bank_auctions.manage') ?? false)) {
            return true;
        }

        return auth()->user()?->hasAdminPermission('properties.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('properties.delete') ?? false);
    }

    /**
     * Auto-generate the next sequential 3-digit Property Code (e.g. 033, 034...)
     */
    public static function generateNextPropertyCode(): string
    {
        $maxNum = 32;
        $allCodes = Property::withTrashed()->pluck('property_code')->toArray();
        foreach ($allCodes as $c) {
            if (is_numeric($c) && (int) $c > $maxNum) {
                $maxNum = (int) $c;
            }
        }
        $assignedCode = str_pad((string) ($maxNum + 1), 3, '0', STR_PAD_LEFT);
        while (Property::withTrashed()->where('property_code', $assignedCode)->exists()) {
            $maxNum++;
            $assignedCode = str_pad((string) ($maxNum + 1), 3, '0', STR_PAD_LEFT);
        }
        return $assignedCode;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'Under Review')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                // STEP 1: Basic Information
                Forms\Components\Wizard\Step::make('Basic')
                    ->icon('heroicon-o-information-circle')
                    ->description('Type, code & title')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('property_code')
                                ->label('Property Code (e.g. 032)')
                                ->default(fn () => static::generateNextPropertyCode())
                                ->unique(ignoreRecord: true)
                                ->placeholder('032')
                                ->maxLength(30)
                                ->helperText('Auto-generated 3-digit code for instant search in app. Can be edited.')
                                ->required(),
                            Forms\Components\Select::make('posted_by_role')
                                ->label('Posted By Role')
                                ->options([
                                    'Owner' => 'Owner',
                                    'Employee' => 'Employee (Staff ID)',
                                    'Agent' => 'Real Estate Agent',
                                    'Builder' => 'Builder / Developer',
                                    'Banker' => 'Banker (Auction)',
                                ])
                                ->default(fn () => auth()->user()?->isEmployee() ? 'Employee' : 'Owner')
                                ->disabled(fn () => auth()->user()?->isEmployee())
                                ->dehydrated()
                                ->required(),
                        ]),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id() ?? 1),

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
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state === 'Bank Auction') {
                                        $set('is_bank_auction', true);
                                    } else {
                                        $set('is_bank_auction', false);
                                    }
                                })
                                ->required(),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('property_sub_type')
                                ->label('Category')
                                ->options(fn (Get $get): array => $get('asset_type') === 'Vehicle' ? [
                                    'Small Commercial Vehicle' => 'Small Commercial Vehicle (Tata Ace, Bolero Maxi)',
                                    '3 Wheeler' => '3 Wheeler (Auto Rickshaw / Cargo 3W)',
                                    '2 Wheeler' => '2 Wheeler (Motorcycle / Scooter)',
                                    'Passenger Car / Hatchback / Sedan' => 'Passenger Car (Hatchback / Sedan)',
                                    'SUV / MUV' => 'SUV / MUV (Innova, Scorpio, Creta, etc.)',
                                    'Commercial Truck' => 'Commercial Truck (Tipper, Trailer, 10-16 Wheeler)',
                                    'Bus / Tempo Traveller' => 'Bus / School Bus / Tempo Traveller',
                                    'Construction / JCB / Crane' => 'Construction Equipment (JCB, Crane, Loader)',
                                    'Tractor / Agro Vehicle' => 'Tractor / Agricultural Equipment',
                                    'Electric Vehicle (EV)' => 'Electric Vehicle (EV Car / 3W / Bike)',
                                    'Luxury Car' => 'Luxury / Premium Vehicle (BMW, Audi, Mercedes)',
                                    'Other' => 'Other Vehicle (Specify below)',
                                ] : [
                                    'Apartment / Flat' => 'Apartment / Flat',
                                    'Row House / Tenement' => 'Row House / Tenement',
                                    'Bungalow / Villa' => 'Bungalow / Villa',
                                    'Farmhouse' => 'Farmhouse',
                                    'Penthouse' => 'Penthouse',
                                    'Studio Apartment / 1 RK' => 'Studio Apartment / 1 RK',
                                    'Duplex' => 'Duplex',
                                    'Residential Plot / NA' => 'Residential Plot / NA Land',
                                    'Commercial Shop' => 'Commercial Shop',
                                    'Office Space' => 'Office Space',
                                    'Showroom' => 'Commercial Showroom',
                                    'Warehouse / Godown' => 'Warehouse / Godown',
                                    'Industrial / Factory' => 'Industrial / Factory Shed',
                                    'Commercial Land / Plot' => 'Commercial Land / Plot',
                                    'Co-working Space' => 'Co-working / Shared Office',
                                    'Hotel / Resort / Banquet' => 'Hotel / Resort / Banquet',
                                    'Agricultural Land' => 'Agricultural Land / Farm',
                                    'Other' => 'Other (Specify below)',
                                ])
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                    if ($get('asset_type') === 'Vehicle' && filled($state) && $state !== 'Other') {
                                        $set('vehicle_category', $state);
                                    }
                                })
                                ->required(),

                            Forms\Components\TextInput::make('custom_category')
                                ->label('Specify Category')
                                ->placeholder('e.g. Warehouse, Studio, Penthouse, Chhota Hathi')
                                ->visible(fn (Get $get): bool => $get('property_sub_type') === 'Other')
                                ->required(fn (Get $get): bool => $get('property_sub_type') === 'Other'),
                        ]),

                        Forms\Components\TextInput::make('title')
                            ->label(fn (Get $get): string => $get('asset_type') === 'Vehicle' ? 'Vehicle Title' : 'Property Title')
                            ->placeholder(fn (Get $get): string => $get('asset_type') === 'Vehicle' ? 'e.g. 2022 Tata Signa 2823.K Tipper (Low KM)' : 'e.g. 3 BHK Ultra Luxury Apartment in Vesu')
                            ->required()
                            ->maxLength(220),

                        Forms\Components\Textarea::make('description')
                            ->label(fn (Get $get): string => $get('asset_type') === 'Vehicle' ? 'Vehicle Description (Optional)' : 'Full Description')
                            ->placeholder(fn (Get $get): string => $get('asset_type') === 'Vehicle' ? 'Describe vehicle condition, tyres, RC status, engine health, etc. (Optional)' : 'Describe the property, features, nearby landmarks, etc.')
                            ->rows(4),
                    ]),

                // STEP 2: Pricing & Location
                Forms\Components\Wizard\Step::make('Price')
                    ->icon('heroicon-o-currency-rupee')
                    ->description('Pricing & address')
                    ->schema([
                        Forms\Components\Grid::make(['default' => 1, 'md' => 3])->schema([
                            Forms\Components\TextInput::make('price')
                                ->label('Actual Price (Numeric)')
                                ->numeric()
                                ->prefix('₹')
                                ->placeholder('12500000')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
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
                                        $area = $get('area_value');
                                        if ($area && is_numeric($area) && $area > 0) {
                                            $set('price_per_sqft', number_format(round($state / $area)));
                                        }
                                    }
                                }),
                            Forms\Components\TextInput::make('price_display')
                                ->label('Display Price (Formatted)')
                                ->placeholder('e.g. ₹ 1.25 Cr')
                                ->required(),
                            Forms\Components\TextInput::make('price_per_sqft')
                                ->label('Price Per Sq.Ft')
                                ->prefix('₹')
                                ->suffix('/sq.ft')
                                ->placeholder('e.g. 6,500')
                                ->visible(fn (Get $get): bool => $get('asset_type') !== 'Vehicle'),
                        ]),

                        Forms\Components\Grid::make(['default' => 1, 'md' => 3])
                            ->visible(fn (Get $get): bool => $get('intent') === 'Rent')
                            ->schema([
                                Forms\Components\TextInput::make('security_deposit')
                                    ->label('Security Deposit (₹)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->placeholder('50000')
                                    ->helperText('Refundable deposit amount.'),
                                Forms\Components\TextInput::make('maintenance_charges')
                                    ->label('Maintenance Charges')
                                    ->placeholder('e.g. ₹ 2,000 / Month or Included'),
                                Forms\Components\Select::make('preferred_tenant')
                                    ->label('Preferred Tenant')
                                    ->options([
                                        'Family Only' => 'Family Only',
                                        'Bachelors Allowed' => 'Bachelors (Male / Female)',
                                        'Company Lease' => 'Company Lease / Corporate',
                                        'Anyone' => 'Anyone (No Preference)',
                                    ])
                                    ->default('Anyone'),
                            ]),

                        Forms\Components\Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                            Forms\Components\Select::make('state')
                                ->label('State')
                                ->options([
                                    'Gujarat' => 'Gujarat',
                                    'Maharashtra' => 'Maharashtra',
                                    'Rajasthan' => 'Rajasthan',
                                    'Madhya Pradesh' => 'Madhya Pradesh',
                                    'Delhi NCR' => 'Delhi NCR',
                                    'Karnataka' => 'Karnataka',
                                    'Telangana' => 'Telangana',
                                    'Tamil Nadu' => 'Tamil Nadu',
                                    'Uttar Pradesh' => 'Uttar Pradesh',
                                    'Haryana' => 'Haryana',
                                    'Punjab' => 'Punjab',
                                    'Goa' => 'Goa',
                                    'Other' => 'Other State',
                                ])
                                ->searchable()
                                ->default('Gujarat')
                                ->required(),
                            Forms\Components\Select::make('city')
                                ->label('City')
                                ->options(function (?Property $record): array {
                                    $cities = City::pluck('name', 'name')->toArray();
                                    if ($record && filled($record->city) && ! isset($cities[$record->city])) {
                                        $cities[$record->city] = $record->city;
                                    }

                                    return $cities;
                                })
                                ->searchable()
                                ->default('Surat')
                                ->required(),
                            Forms\Components\TextInput::make('locality')
                                ->label('Locality / Area')
                                ->placeholder('e.g. Vesu, Adajan, Pal')
                                ->required(),
                            Forms\Components\TextInput::make('pincode')
                                ->label('Pincode')
                                ->placeholder('e.g. 395007')
                                ->required()
                                ->numeric()
                                ->length(6)
                                ->helperText('6-digit postal pincode is mandatory.'),
                        ]),

                        Forms\Components\Grid::make(['default' => 1, 'md' => 3])->schema([
                            Forms\Components\TextInput::make('society_or_yard')
                                ->label(fn (Get $get) => $get('asset_type') === 'Vehicle' ? 'Yard / Parking Location' : 'Society / Building / Yard Name')
                                ->placeholder('e.g. Avadh Copper Stone / Ring Road Yard')
                                ->visible(fn (Get $get): bool => !($get('asset_type') === 'Vehicle' && ($get('is_bank_auction') || $get('intent') === 'Bank Auction'))),
                            Forms\Components\TextInput::make('contact_phone')
                                ->label('Primary Contact Phone (Mandatory)')
                                ->tel()
                                ->placeholder('+91 98765 43210')
                                ->required()
                                ->helperText('Main calling number for buyers/inquiries.'),
                            Forms\Components\TextInput::make('alt_contact_phone')
                                ->label('Secondary / Alternate Phone (Optional)')
                                ->tel()
                                ->placeholder('+91 98765 00000')
                                ->helperText('Optional 2nd number or WhatsApp contact.'),
                        ]),

                        Forms\Components\Toggle::make('is_negotiable')
                            ->label('Price is Negotiable')
                            ->default(true),
                    ]),

                // STEP 3: Specifications
                Forms\Components\Wizard\Step::make('Specs')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->description('Dimensions & features')
                    ->schema([
                        // Property-specific fields
                        Forms\Components\Section::make('Real Estate Specs')
                            ->visible(fn (Get $get): bool => $get('asset_type') !== 'Vehicle')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->visible(fn (Get $get): bool => !in_array($get('property_sub_type'), [
                                        'Residential Plot / NA',
                                        'Residential Plot / NA Land',
                                        'Commercial Land / Plot',
                                        'Agricultural Land',
                                        'Agricultural Land / Farm',
                                    ]))
                                    ->schema([
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

                                Forms\Components\Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                                    Forms\Components\TextInput::make('area_value')
                                        ->label('Carpet / Primary Area')
                                        ->numeric()
                                        ->placeholder('1850')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                            $price = $get('price');
                                            if ($state && is_numeric($state) && $state > 0 && $price && is_numeric($price)) {
                                                $set('price_per_sqft', number_format(round($price / $state)));
                                            }
                                        }),
                                    Forms\Components\TextInput::make('super_area_value')
                                        ->label('Super Built-up Area')
                                        ->numeric()
                                        ->placeholder('2400')
                                        ->visible(fn (Get $get): bool => !in_array($get('property_sub_type'), [
                                            'Residential Plot / NA',
                                            'Residential Plot / NA Land',
                                            'Commercial Land / Plot',
                                            'Agricultural Land',
                                            'Agricultural Land / Farm',
                                        ])),
                                    Forms\Components\Select::make('area_unit')
                                        ->label('Area Unit')
                                        ->options([
                                            'Sq.Ft' => 'Sq.Ft (Square Feet)',
                                            'Var (Sq.Yard)' => 'Var / Sq.Yard',
                                            'Sq.Meter' => 'Sq.Meter (Square Meter)',
                                            'Acre' => 'Acre',
                                            'Bigha / Vigha' => 'Bigha / Vigha',
                                            'Guntha' => 'Guntha',
                                            'Hectare' => 'Hectare',
                                            'Gaj' => 'Gaj',
                                            'Katha' => 'Katha',
                                            'Foot' => 'Foot / Running Feet',
                                        ])
                                        ->default('Sq.Ft')
                                        ->searchable(),
                                    Forms\Components\Select::make('area_type')
                                        ->label('Area Type')
                                        ->options([
                                            'Carpet Area' => 'Carpet Area (RERA Net Usable)',
                                            'Super Built-up Area' => 'Super Built-up Area (SBA)',
                                            'Built-up Area' => 'Built-up Area (Covered)',
                                            'Plot Area' => 'Plot Area (Ground Area)',
                                            'Salable Area' => 'Salable Area',
                                            'Undivided Share (UDS)' => 'Undivided Land Share (UDS)',
                                            'Gross Leaseable Area' => 'Gross Leaseable Area (GLA)',
                                        ])
                                        ->default(fn (Get $get): string => in_array($get('property_sub_type'), [
                                            'Residential Plot / NA',
                                            'Residential Plot / NA Land',
                                            'Commercial Land / Plot',
                                            'Agricultural Land',
                                            'Agricultural Land / Farm',
                                        ]) ? 'Plot Area' : 'Super Built-up Area'),
                                ]),

                                Forms\Components\Grid::make(2)
                                    ->visible(fn (Get $get): bool => in_array($get('property_sub_type'), [
                                        'Residential Plot / NA',
                                        'Residential Plot / NA Land',
                                        'Commercial Land / Plot',
                                        'Agricultural Land',
                                        'Agricultural Land / Farm',
                                    ]))
                                    ->schema([
                                        Forms\Components\TextInput::make('plot_frontage')
                                            ->label('Road Width / Plot Frontage')
                                            ->placeholder('e.g. 40 Ft Road Facing, Corner Plot'),
                                        Forms\Components\Select::make('boundary_wall')
                                            ->label('Boundary Wall / Fencing')
                                            ->options([
                                                'Constructed Wall' => 'Constructed Wall (Pakka Boundary)',
                                                'Fenced' => 'Wire / Pole Fencing',
                                                'Open' => 'Open Boundary (No Wall)',
                                            ])
                                            ->default('Constructed Wall'),
                                    ]),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('furnishing')
                                        ->label('Furnishing Status')
                                        ->visible(fn (Get $get): bool => !in_array($get('property_sub_type'), [
                                            'Residential Plot / NA',
                                            'Residential Plot / NA Land',
                                            'Commercial Land / Plot',
                                            'Agricultural Land',
                                            'Agricultural Land / Farm',
                                        ]))
                                        ->options([
                                            'Unfurnished' => 'Unfurnished',
                                            'Semi-Furnished' => 'Semi-Furnished',
                                            'Fully Furnished' => 'Fully Furnished',
                                        ])
                                        ->default('Unfurnished'),
                                    Forms\Components\Select::make('facing')
                                        ->label('Facing Direction (Vastu)')
                                        ->options([
                                            'East' => 'East (Ugatani Disha)',
                                            'North' => 'North (Uttar)',
                                            'North-East' => 'North-East (Ishan)',
                                            'West' => 'West (Pashchim)',
                                            'South' => 'South (Dakshin)',
                                            'North-West' => 'North-West (Vayavya)',
                                            'South-East' => 'South-East (Agni)',
                                            'South-West' => 'South-West (Nairutya)',
                                        ]),
                                ]),

                                Forms\Components\Select::make('property_age')
                                    ->label('Age of Property / Construction Stage')
                                    ->options([
                                        'Under Construction' => 'Under Construction',
                                        'New / Ready to Move (0-1 Yrs)' => 'New / Ready to Move (0-1 Yrs)',
                                        '1 to 5 Years Old' => '1 to 5 Years Old',
                                        '5 to 10 Years Old' => '5 to 10 Years Old',
                                        '10+ Years Old' => '10+ Years Old',
                                    ])
                                    ->default('New / Ready to Move (0-1 Yrs)'),

                                Forms\Components\TagsInput::make('amenities')
                                    ->label('Amenities')
                                    ->placeholder('Type and hit Enter (e.g. Lift, Parking, Gym, Pool)')
                                    ->suggestions([
                                        'Lift', 'Covered Parking', 'Open Parking', 'Gym', 'Swimming Pool',
                                        'Club House', 'Garden / Park', '24x7 Security', 'CCTV', 'Power Backup',
                                        'Children Play Area', 'Fire Safety', 'Intercom', 'Water Supply 24x7',
                                    ]),
                            ]),

                        // Vehicle-specific fields (Bank Auction & Commercial / Private Vehicles)
                        Forms\Components\Section::make('Vehicle Specifications & Inspection')
                            ->visible(fn (Get $get): bool => $get('asset_type') === 'Vehicle')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('vehicle_category')
                                        ->label('Vehicle Category')
                                        ->options([
                                            'Small Commercial Vehicle' => 'Small Commercial Vehicle (Tata Ace, Bolero Maxi)',
                                            '3 Wheeler' => '3 Wheeler (Auto Rickshaw / Cargo 3W)',
                                            '2 Wheeler' => '2 Wheeler (Motorcycle / Scooter)',
                                            'Passenger Car / Hatchback / Sedan' => 'Passenger Car (Hatchback / Sedan)',
                                            'SUV / MUV' => 'SUV / MUV (Innova, Scorpio, Creta, etc.)',
                                            'Commercial Truck' => 'Commercial Truck (Tipper, Heavy Trailer)',
                                            'Bus / Tempo Traveller' => 'Bus / School Bus / Tempo Traveller',
                                            'Construction / JCB / Crane' => 'Construction Equipment (JCB, Crane, Loader)',
                                            'Tractor / Agro Vehicle' => 'Tractor / Agricultural Equipment',
                                            'Electric Vehicle (EV)' => 'Electric Vehicle (EV Car / 3W / Bike)',
                                            'Luxury Car' => 'Luxury / Premium Vehicle (BMW, Audi, Mercedes)',
                                            'Other' => 'Other Vehicle',
                                        ])
                                        ->searchable()
                                        ->default(fn (Get $get) => $get('property_sub_type'))
                                        ->required(fn (Get $get): bool => $get('asset_type') === 'Vehicle'),

                                    Forms\Components\Select::make('vehicle_brand')
                                        ->label('Vehicle Brand / Make')
                                        ->options([
                                            'Ashok Leyland' => 'Ashok Leyland',
                                            'Tata Motors' => 'Tata Motors',
                                            'Mahindra & Mahindra' => 'Mahindra & Mahindra',
                                            'Maruti Suzuki' => 'Maruti Suzuki',
                                            'Hyundai' => 'Hyundai',
                                            'Toyota' => 'Toyota',
                                            'Eicher' => 'Eicher Trucks & Buses',
                                            'BharatBenz' => 'BharatBenz',
                                            'Force Motors' => 'Force Motors',
                                            'Isuzu' => 'Isuzu',
                                            'Honda' => 'Honda',
                                            'Hero MotoCorp' => 'Hero MotoCorp',
                                            'Bajaj Auto' => 'Bajaj Auto',
                                            'TVS Motor' => 'TVS Motor',
                                            'JCB' => 'JCB India',
                                            'Other Brand' => 'Other Brand / Manufacturer',
                                        ])
                                        ->live()
                                        ->searchable(),

                                    Forms\Components\TextInput::make('custom_vehicle_brand')
                                        ->label('Specify Brand Name')
                                        ->placeholder('e.g. Kia, MG, Skoda, Volvo')
                                        ->visible(fn (Get $get): bool => $get('vehicle_brand') === 'Other Brand')
                                        ->required(fn (Get $get): bool => $get('vehicle_brand') === 'Other Brand'),

                                    Forms\Components\TextInput::make('vehicle_model')
                                        ->label('Model Name')
                                        ->placeholder('e.g. Bada Dost i2, Signa 2823.K, Bolero Pik-Up')
                                        ->required(fn (Get $get): bool => $get('asset_type') === 'Vehicle'),
                                ]),

                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('vehicle_variant')
                                        ->label('Variant / Trim')
                                        ->placeholder('e.g. LS, VXi, ZXi, Base, HD'),

                                    Forms\Components\Select::make('body_type')
                                        ->label('Body Type')
                                        ->options([
                                            'Pickup' => 'Pickup Truck',
                                            'Tipper / Dumper' => 'Tipper / Dumper',
                                            'Closed Container' => 'Closed Container',
                                            'Open Body Truck' => 'Open Body Truck',
                                            'Hatchback' => 'Hatchback',
                                            'Sedan' => 'Sedan',
                                            'SUV / MUV' => 'SUV / MUV',
                                            'Bus / Van' => 'Bus / Van',
                                            'Chassis Cabin' => 'Chassis Cabin (Without Body)',
                                            'Tanker' => 'Tanker',
                                            'Trailer' => 'Trailer / Flatbed',
                                            'Other' => 'Other Body Type',
                                        ])
                                        ->searchable(),

                                    Forms\Components\Select::make('vehicle_color')
                                        ->label('Vehicle Color')
                                        ->options([
                                            'White' => 'White',
                                            'Silver' => 'Silver',
                                            'Black' => 'Black',
                                            'Grey' => 'Grey',
                                            'Blue' => 'Blue',
                                            'Red' => 'Red',
                                            'Yellow' => 'Yellow',
                                            'Brown' => 'Brown',
                                            'Golden' => 'Golden',
                                            'Green' => 'Green',
                                            'Orange' => 'Orange',
                                            'Other' => 'Other / Custom Color',
                                        ])
                                        ->searchable()
                                        ->placeholder('Select color'),
                                ]),

                                Forms\Components\Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                                    Forms\Components\TextInput::make('vehicle_reg_number')
                                        ->label('Registration (RTO No)')
                                        ->placeholder('e.g. JK01AS7109 / GJ05XX1234')
                                        ->unique(ignoreRecord: true)
                                        ->dehydrateStateUsing(fn ($state) => filled($state) ? strtoupper(trim($state)) : null)
                                        ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                        ->required(fn (Get $get): bool => $get('asset_type') === 'Vehicle'),

                                    Forms\Components\TextInput::make('engine_number')
                                        ->label('Engine Number')
                                        ->placeholder('e.g. BN******0P'),

                                    Forms\Components\TextInput::make('chassis_number')
                                        ->label('Chassis Number')
                                        ->placeholder('e.g. MB*************23'),

                                    Forms\Components\TextInput::make('engine_capacity_cc')
                                        ->label('Engine Capacity (CC)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->suffix('cc')
                                        ->placeholder('e.g. 1478'),
                                ]),

                                Forms\Components\Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                                    Forms\Components\TextInput::make('vehicle_make_year')
                                        ->label('Mfg Year')
                                        ->numeric()
                                        ->minValue(1980)
                                        ->maxValue(2027)
                                        ->placeholder('2022'),

                                    Forms\Components\TextInput::make('vehicle_km_driven')
                                        ->label('Odometer (KM Driven)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->suffix('km')
                                        ->placeholder('35980'),

                                    Forms\Components\Select::make('vehicle_fuel_type')
                                        ->label('Fuel Type')
                                        ->options([
                                            'Diesel' => 'Diesel',
                                            'Petrol' => 'Petrol',
                                            'CNG' => 'CNG',
                                            'Electric' => 'Electric (EV)',
                                            'Hybrid' => 'Hybrid',
                                        ]),

                                    Forms\Components\Select::make('vehicle_transmission')
                                        ->label('Transmission')
                                        ->options([
                                            'Manual' => 'Manual',
                                            'Automatic' => 'Automatic',
                                        ]),
                                ]),

                                Forms\Components\Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                                    Forms\Components\Select::make('plate_type')
                                        ->label('License Plate Type')
                                        ->options([
                                            'Commercial' => 'Commercial (Yellow Plate)',
                                            'Private' => 'Private (White Plate)',
                                        ])
                                        ->placeholder('Select Plate Type'),

                                    Forms\Components\Select::make('vehicle_owner_type')
                                        ->label('Ownership')
                                        ->options([
                                            'First Owner' => 'First Owner',
                                            'Second Owner' => 'Second Owner',
                                            'Third Owner' => 'Third Owner',
                                            'Bank Seized / Hypothecated' => 'Bank Seized (Hypothecated)',
                                        ])
                                        ->default('First Owner'),

                                    Forms\Components\Select::make('rc_status')
                                        ->label('RC Book Availability')
                                        ->options([
                                            'Original RC Available' => 'Original RC Book Available',
                                            'RC Copy / Form 35' => 'RC Copy + Bank Form 35 Available',
                                            'Duplicate RC in Process' => 'Duplicate RC in Process',
                                            'RC Not Available' => 'RC Not Available / Lost',
                                        ])
                                        ->default('RC Copy / Form 35'),

                                    Forms\Components\Select::make('insurance_status')
                                        ->label('Insurance Status')
                                        ->options([
                                            'Valid Comprehensive' => 'Valid Comprehensive Insurance',
                                            'Valid Third-Party' => 'Valid Third-Party Insurance',
                                            'Expired' => 'Expired',
                                            'Not Available' => 'Not Available',
                                        ])
                                        ->default('Expired'),
                                ]),

                                Forms\Components\Select::make('ac_working')
                                    ->label('AC Working')
                                    ->options([
                                        'Yes' => 'Yes (Working)',
                                        'No' => 'No (Not Working)',
                                        'Non-AC' => 'Non-AC Vehicle',
                                    ])
                                    ->default('No'),

                                Forms\Components\Textarea::make('vehicle_description')
                                    ->label('Vehicle Description & Condition Notes (Optional)')
                                    ->placeholder('Engine health, battery status (e.g. Exide), tyre condition, body condition, floor mat, etc.')
                                    ->rows(3),

                                Forms\Components\TagsInput::make('vehicle_inspection_features')
                                    ->label('Inspection Checklist Tags')
                                    ->placeholder('Add detail (e.g. Front Tyres Average, Battery Exide, Music System No, Clean Chassis)')
                                    ->suggestions([
                                        'Front Tyres Good', 'Rear Tyres Good', 'Battery Good', 'Engine Running',
                                        'Clean Chassis', 'Original Paint', 'Keys Available', 'AC Working',
                                        'Power Steering', 'Music System',
                                    ]),
                            ]),
                    ]),

                // STEP 4: Bank Auction (Optional)
                Forms\Components\Wizard\Step::make('Auction')
                    ->icon('heroicon-o-scale')
                    ->description('SARFAESI / Seized Asset')
                    ->schema([
                        Forms\Components\Toggle::make('is_bank_auction')
                            ->label('Mark as Bank Auction / Seized Asset')
                            ->onColor('warning')
                            ->offColor('gray')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                if (!$state && $get('intent') === 'Bank Auction') {
                                    $set('intent', 'Sell');
                                } elseif ($state && $get('intent') !== 'Bank Auction') {
                                    $set('intent', 'Bank Auction');
                                }
                            }),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('auction_by')
                                ->label('Auction Held By / Authority')
                                ->options([
                                    'Bank (SARFAESI)' => 'Bank (SARFAESI Act 2002 - Bank Self-Auction)',
                                    'DRT' => 'DRT (Debt Recovery Tribunal)',
                                    'NCLT' => 'NCLT (Insolvency & Bankruptcy Code / IBC)',
                                    'ARC' => 'ARC (Asset Reconstruction Company)',
                                    'NBFC' => 'NBFC / Housing Finance Company',
                                    'Court Receiver' => 'Court Receiver / Official Liquidator',
                                ])
                                ->default('Bank (SARFAESI)')
                                ->required(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),

                            Forms\Components\TextInput::make('bank_asset_id')
                                ->label('Bank Asset ID / Notice Code')
                                ->placeholder('e.g. SBINJK01AS7109')
                                ->helperText('Official bank tracking ID from auction tender notice.')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),

                            Forms\Components\Select::make('action_type')
                                ->label('Type of Action / Legal Clause')
                                ->options([
                                    'Under Hypothecation' => 'Under Hypothecation (Auto/Vehicle Loan)',
                                    'SARFAESI Section 13(4)' => 'SARFAESI Act Sec 13(4) (Physical Seizure)',
                                    'DRT Recovery Order' => 'DRT Recovery Order (RDDBFI Act)',
                                    'NCLT Liquidation' => 'NCLT Liquidation / Resolution Process (IBC)',
                                    'Court Attachment' => 'Court Attachment / Execution Proceeding',
                                ])
                                ->default('Under Hypothecation')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                        ]),

                        Forms\Components\Grid::make(['default' => 1, 'md' => 2])->schema([
                            Forms\Components\TextInput::make('bank_name')
                                ->label('Bank Name')
                                ->placeholder('e.g. State Bank of India, Bank of Baroda')
                                ->required(fn (Get $get): bool => $get('is_bank_auction') || $get('intent') === 'Bank Auction')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('borrower_name')
                                ->label('Borrower / NPA Account')
                                ->placeholder('Borrower / Firm / Company Name')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('borrower_loan_account')
                                ->label('Loan Account / Seized From Details')
                                ->placeholder('e.g. Loan A/C 394857210 / Seized from Mr. Patel')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('reserve_price')
                                ->label('Reserve Price (₹)')
                                ->numeric()
                                ->prefix('₹')
                                ->placeholder('5000000')
                                ->default(fn (Get $get) => $get('price'))
                                ->required(fn (Get $get): bool => $get('is_bank_auction') || $get('intent') === 'Bank Auction')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                    if ($state && is_numeric($state)) {
                                        $set('emd_amount', round($state * 0.10));
                                        if (blank($get('price'))) {
                                            $set('price', $state);
                                        }
                                    }
                                })
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('emd_amount')
                                ->label('EMD Amount (₹)')
                                ->numeric()
                                ->prefix('₹')
                                ->placeholder('500000')
                                ->helperText('Earnest Money Deposit (usually 10% of Reserve Price)')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('emd_last_date')
                                ->label('EMD Submission Last Date & Time')
                                ->placeholder('e.g. 24 Nov 2026 by 05:00 PM')
                                ->helperText('Auto-filled when you pick exact deadline, or type manually.')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\DateTimePicker::make('emd_last_timestamp')
                                ->label('EMD Exact Deadline (Date & Time)')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $set('emd_last_date', \Carbon\Carbon::parse($state)->format('d M Y h:i A'));
                                    }
                                })
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('auction_date')
                                ->label('Auction Date (Text Display)')
                                ->placeholder('e.g. 25 Nov 2026 11:00 AM')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\DateTimePicker::make('auction_timestamp')
                                ->label('Auction Exact Date/Time')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $set('auction_date', \Carbon\Carbon::parse($state)->format('d M Y h:i A'));
                                    }
                                })
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\Select::make('possession_type')
                                ->label('Possession Type (SARFAESI)')
                                ->options([
                                    'Physical Possession' => 'Physical Possession (Vacant & Ready Handover)',
                                    'Symbolic Possession' => 'Symbolic Possession (Legal Notice / Section 13(4))',
                                    'Constructive Possession' => 'Constructive Possession (CMM/DM Order in Process)',
                                    'Sec 14 Assistance' => 'Sec 14 Police / Magistrate Assistance',
                                    'Pre-Possession Stage' => 'Notice Stage / Under Process',
                                ])
                                ->default('Physical Possession')
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
                            Forms\Components\TextInput::make('bank_branch_or_yard')
                                ->label('Branch / Vehicle Yard')
                                ->placeholder('e.g. SARB Branch, Ring Road, Surat')
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('notice_pdf_url')
                                ->label('Auction Notice URL / PDF Document')
                                ->url()
                                ->placeholder('https://... (Direct Link to Bank Sale Notice PDF)')
                                ->helperText('Official bank tender / SARFAESI auction notice link.')
                                ->columnSpan(['default' => 1, 'md' => 2])
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                            Forms\Components\TextInput::make('auction_portal_url')
                                ->label('e-Auction Bidding Portal URL')
                                ->url()
                                ->placeholder('https://ibapi.in/... or bank e-auction link')
                                ->helperText('Online bidding / tender portal link.')
                                ->columnSpan(['default' => 1, 'md' => 2])
                                ->visible(fn (Get $get) => $get('is_bank_auction') || $get('intent') === 'Bank Auction'),
                        ]),
                    ]),

                // STEP 5: Photos & Status
                Forms\Components\Wizard\Step::make('Photos')
                    ->icon('heroicon-o-photo')
                    ->description('Pictures & status')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->label('Property Photos')
                            ->relationship('images')
                            ->schema([
                                Forms\Components\TextInput::make('image_url')
                                    ->label('Photo URL / Image Link')
                                    ->placeholder('https://images.unsplash.com/... or image path')
                                    ->default('https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80')
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80')
                                    ->required()
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
                            ->defaultItems(0)
                            ->collapsible()
                            ->reorderable('sort_order'),

                        Forms\Components\Grid::make(['default' => 1, 'md' => 3])->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'Active' => 'Active (Live on App)',
                                    'Under Review' => 'Under Review',
                                    'Sold / Rented' => 'Sold / Rented',
                                    'Rejected' => 'Rejected',
                                ])
                                ->default(fn () => (auth()->user()?->isEmployee() && !auth()->user()?->hasAdminPermission('properties.approve_reject')) ? 'Under Review' : 'Active')
                                ->disabled(fn () => auth()->user()?->isEmployee() && !auth()->user()?->hasAdminPermission('properties.approve_reject'))
                                ->dehydrated()
                                ->live()
                                ->required(),
                            Forms\Components\Toggle::make('is_verified')
                                ->label('Verified Badge')
                                ->default(fn () => auth()->user()?->isOwner() || auth()->user()?->hasAdminPermission('properties.approve_reject'))
                                ->disabled(fn () => auth()->user()?->isEmployee() && !auth()->user()?->hasAdminPermission('properties.approve_reject'))
                                ->dehydrated(),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured (Top of Feed)')
                                ->disabled(fn () => auth()->user()?->isEmployee())
                                ->dehydrated()
                                ->live()
                                ->default(false),
                        ]),

                        Forms\Components\Grid::make(['default' => 1, 'md' => 2])->schema([
                            Forms\Components\DatePicker::make('sold_date')
                                ->label('Sold / Rented Date')
                                ->visible(fn (Get $get): bool => $get('status') === 'Sold / Rented'),
                            Forms\Components\DatePicker::make('featured_until')
                                ->label('Featured Until Date')
                                ->visible(fn (Get $get): bool => (bool) $get('is_featured')),
                        ]),

                        Forms\Components\Textarea::make('admin_review_note')
                            ->label(fn (Get $get): string => $get('status') === 'Rejected' ? 'Rejection Reason (Visible to applicant in App)' : 'Internal Admin Notes')
                            ->placeholder(fn (Get $get): string => $get('status') === 'Rejected' ? 'State clear rejection reason so applicant can correct their listing...' : 'Any internal notes about this listing...')
                            ->required(fn (Get $get): bool => $get('status') === 'Rejected')
                            ->helperText(fn (Get $get): ?string => $get('status') === 'Rejected' ? 'This reason is displayed to the user inside their mobile app under My Listings.' : null)
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
                Tables\Columns\TextColumn::make('asset_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Vehicle' => 'warning',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('locality')
                    ->label('Location')
                    ->description(fn (Property $record): string => $record->city . ($record->state ? (', ' . $record->state) : ''))
                    ->searchable(['locality', 'city', 'state']),
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
                    })
                    ->description(fn (Property $record): ?string => $record->status === 'Rejected' ? $record->admin_review_note : null),
                Tables\Columns\IconColumn::make('is_bank_auction')
                    ->label('Auction')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('auction_status')
                    ->label('Auction Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Live' => 'success',
                        'Upcoming' => 'warning',
                        'Concluded' => 'gray',
                        default => 'secondary',
                    })
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
                    ->description(fn (Property $record): ?string => $record->creator ? ('By: ' . ($record->creator->employee_code ?: $record->creator->name)) : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Added By')
                    ->badge()
                    ->color(fn (Property $record): string => match (true) {
                        $record->creator?->isEmployee() => 'info',
                        $record->creator?->isOwner() => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state, Property $record): string {
                        if ($record->creator) {
                            return ($record->creator->employee_code ? '[' . $record->creator->employee_code . '] ' : '') . $record->creator->name;
                        }
                        if ($record->user) {
                            return '[App] ' . $record->user->name;
                        }
                        return 'Self / App';
                    })
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('created_by_user_id')
                    ->label('Added By (Staff / Owner)')
                    ->options(function () {
                        return \App\Models\User::where(function ($query) {
                            $query->whereIn('role', ['admin', 'owner', 'superadmin', 'SuperAdmin', 'Admin'])
                                  ->orWhereIn('role', ['Employee', 'employee', 'Staff', 'staff', 'Manager', 'manager', 'Telecaller', 'telecaller', 'Field Executive', 'field executive']);
                        })
                            ->get()
                            ->mapWithKeys(fn ($user) => [
                                $user->id => ($user->employee_code ? "[{$user->employee_code}] " : '') . $user->name . " ({$user->role})"
                            ])
                            ->toArray();
                    })
                    ->searchable(),
                Tables\Filters\Filter::make('added_today')
                    ->label('Added Today')
                    ->query(fn ($query) => $query->whereDate('created_at', \Carbon\Carbon::today())),
                Tables\Filters\Filter::make('my_entries')
                    ->label('My Entries Only')
                    ->query(fn ($query) => $query->where(fn ($q) => $q->where('created_by_user_id', auth()->id())->orWhere('user_id', auth()->id())))
                    ->visible(fn () => auth()->user()?->isEmployee()),
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
                Tables\Filters\SelectFilter::make('auction_status')
                    ->label('Auction Status')
                    ->options([
                        'Upcoming' => 'Upcoming',
                        'Live' => 'Live Now',
                        'Concluded' => 'Concluded / Closed',
                    ]),
                Tables\Filters\SelectFilter::make('auction_by')
                    ->label('Auction Authority')
                    ->options([
                        'Bank (SARFAESI)' => 'Bank (SARFAESI)',
                        'DRT' => 'DRT (Debt Recovery Tribunal)',
                        'NCLT' => 'NCLT (IBC Liquidation)',
                        'ARC' => 'ARC',
                        'NBFC' => 'NBFC',
                        'Court Receiver' => 'Court Receiver',
                    ]),
                Tables\Filters\SelectFilter::make('state')
                    ->options([
                        'Gujarat' => 'Gujarat',
                        'Maharashtra' => 'Maharashtra',
                        'Rajasthan' => 'Rajasthan',
                        'Madhya Pradesh' => 'Madhya Pradesh',
                        'Delhi NCR' => 'Delhi NCR',
                    ]),
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
                        ->visible(fn (Property $record) => $record->status !== 'Active' && (auth()->user()?->isOwner() || auth()->user()?->hasAdminPermission('properties.approve_reject')))
                        ->action(function (Property $record) {
                            $record->update([
                                'status' => 'Active',
                                'is_verified' => true,
                                'admin_review_note' => null,
                                'reviewed_by_user_id' => auth()->id(),
                            ]);
                            Notification::make()
                                ->title('Property Activated')
                                ->body("Property #{$record->property_code} is now Live & Active on the App.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('deactivate')
                        ->label('Deactivate (Inactive)')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Property $record) => $record->status === 'Active' && (auth()->user()?->isOwner() || auth()->user()?->hasAdminPermission('properties.approve_reject')))
                        ->action(function (Property $record) {
                            $record->update([
                                'status' => 'Under Review',
                                'reviewed_by_user_id' => auth()->id(),
                            ]);
                            Notification::make()
                                ->title('Property Inactivated')
                                ->body("Property #{$record->property_code} has been marked Inactive.")
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\Action::make('reject')
                        ->label('Reject Listing')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Property $record) => $record->status !== 'Rejected' && (auth()->user()?->isOwner() || auth()->user()?->hasAdminPermission('properties.approve_reject')))
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Reason for Rejection')
                                ->placeholder('e.g. Incomplete details, invalid contact number, or unclear photos.')
                                ->required(),
                        ])
                        ->action(function (Property $record, array $data) {
                            $record->update([
                                'status' => 'Rejected',
                                'admin_review_note' => $data['rejection_reason'],
                                'reviewed_by_user_id' => auth()->id(),
                            ]);
                            Notification::make()
                                ->title('Property Rejected')
                                ->body("Property #{$record->property_code} has been rejected with reason.")
                                ->danger()
                                ->send();
                        }),

                    Tables\Actions\Action::make('assignCode')
                        ->label('Set Code')
                        ->icon('heroicon-o-identification')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('property_code')
                                ->label('Assign 3-digit Code (e.g. 032)')
                                ->required()
                                ->unique(table: 'properties', column: 'property_code', ignorable: fn (Property $record) => $record)
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('properties.delete') ?? false)),
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
