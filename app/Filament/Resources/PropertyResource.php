<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('PropertyDetails')->tabs([
                // Tab 1: General & Unique Code
                Forms\Components\Tabs\Tab::make('General & Unique Code')->schema([
                    Forms\Components\TextInput::make('property_code')
                        ->label('Property Code (e.g. 032)')
                        ->unique(ignoreRecord: true)
                        ->placeholder('032')
                        ->maxLength(30)
                        ->helperText('Assign a unique short code for fast user lookup.'),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(220)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('intent')
                        ->options([
                            'Sell' => 'Sell',
                            'Rent' => 'Rent',
                            'Bank Auction' => 'Bank Auction',
                        ])
                        ->required(),
                    Forms\Components\Select::make('asset_type')
                        ->options([
                            'Property' => 'Property (Real Estate)',
                            'Vehicle' => 'Vehicle (Bank Seized)',
                        ])
                        ->required()
                        ->reactive(),
                    Forms\Components\TextInput::make('property_sub_type')
                        ->label('Sub-Type')
                        ->placeholder('Apartment / Flat, Commercial Shop, Tipper Truck, SUV')
                        ->required()
                        ->maxLength(80),
                    Forms\Components\Select::make('status')
                        ->options([
                            'Active' => 'Active (Published)',
                            'Under Review' => 'Under Review',
                            'Sold / Rented' => 'Sold / Rented',
                            'Rejected' => 'Rejected',
                        ])
                        ->default('Under Review')
                        ->required(),
                    Forms\Components\Toggle::make('is_verified')->label('Verified Listing')->default(false),
                    Forms\Components\Toggle::make('is_featured')->label('Featured on Feed')->default(false),
                    Forms\Components\Textarea::make('description')->columnSpanFull(),
                ])->columns(2),

                // Tab 2: Pricing & Location
                Forms\Components\Tabs\Tab::make('Pricing & Location')->schema([
                    Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->prefix('₹')
                        ->required(),
                    Forms\Components\TextInput::make('price_display')
                        ->label('Display Price (e.g. ₹ 1.25 Cr)')
                        ->required(),
                    Forms\Components\TextInput::make('price_per_sqft')
                        ->label('Price Per Sq.Ft'),
                    Forms\Components\Toggle::make('is_negotiable')->default(true),
                    Forms\Components\TextInput::make('society_or_yard')
                        ->label('Society / Complex / Yard Name'),
                    Forms\Components\TextInput::make('locality')
                        ->required(),
                    Forms\Components\TextInput::make('city')
                        ->default('Surat')
                        ->required(),
                    Forms\Components\TextInput::make('state')
                        ->default('Gujarat'),
                    Forms\Components\TextInput::make('pincode'),
                    Forms\Components\TextInput::make('contact_phone')
                        ->label('Seller / Officer Contact Phone')
                        ->required(),
                ])->columns(2),

                // Tab 3: Real Estate Specifications
                Forms\Components\Tabs\Tab::make('Real Estate Specs')->schema([
                    Forms\Components\TextInput::make('bhk')->placeholder('2 BHK, 3 BHK, Penthouse'),
                    Forms\Components\TextInput::make('bathrooms')->placeholder('2 Bathrooms'),
                    Forms\Components\TextInput::make('balconies')->placeholder('1 Balcony'),
                    Forms\Components\TextInput::make('area_value')->numeric(),
                    Forms\Components\TextInput::make('area_unit')->default('Sq.Ft'),
                    Forms\Components\TextInput::make('area_type')->default('Super Built-up Area'),
                    Forms\Components\TextInput::make('furnishing')->placeholder('Fully Furnished, Unfurnished'),
                    Forms\Components\TagsInput::make('amenities')
                        ->placeholder('Add amenity (Gym, Swimming Pool, Lift, Parking)'),
                ])->columns(2),

                // Tab 4: Seized Vehicle Specifications
                Forms\Components\Tabs\Tab::make('Seized Vehicle Specs')->schema([
                    Forms\Components\TextInput::make('vehicle_category')->placeholder('Car / SUV, Commercial Truck'),
                    Forms\Components\TextInput::make('vehicle_model')->placeholder('Tata Signa, Mahindra Bolero'),
                    Forms\Components\TextInput::make('vehicle_make_year')->numeric(),
                    Forms\Components\TextInput::make('vehicle_km_driven')->numeric(),
                    Forms\Components\TextInput::make('vehicle_fuel_type')->placeholder('Diesel, Petrol, CNG'),
                    Forms\Components\TextInput::make('vehicle_transmission')->placeholder('Manual, Automatic'),
                    Forms\Components\TextInput::make('vehicle_reg_number')->placeholder('GJ-05-BX-4921'),
                    Forms\Components\TagsInput::make('vehicle_inspection_features')
                        ->placeholder('Engine Condition, Tyre Tread, RC Status'),
                ])->columns(2),

                // Tab 5: Bank Auction & SARFAESI
                Forms\Components\Tabs\Tab::make('Bank Auction / SARFAESI')->schema([
                    Forms\Components\Toggle::make('is_bank_auction')->label('SARFAESI Bank Auction')->default(false),
                    Forms\Components\TextInput::make('bank_name')->placeholder('State Bank of India, Bank of Baroda'),
                    Forms\Components\TextInput::make('auction_date')->placeholder('25 Nov 2026 11:00 AM'),
                    Forms\Components\DateTimePicker::make('auction_timestamp'),
                    Forms\Components\TextInput::make('reserve_price')->numeric()->prefix('₹'),
                    Forms\Components\TextInput::make('emd_amount')->numeric()->prefix('₹'),
                    Forms\Components\TextInput::make('borrower_name')->placeholder('Defaulting Borrower / Company'),
                    Forms\Components\TextInput::make('bank_branch_or_yard')->placeholder('SARB Branch or Yard Address'),
                    Forms\Components\TextInput::make('notice_pdf_url')->placeholder('https://.../notice.pdf')->url(),
                    Forms\Components\Select::make('auction_status')
                        ->options([
                            'Upcoming' => 'Upcoming',
                            'Live' => 'Live Now',
                            'Concluded' => 'Concluded / Closed',
                        ])
                        ->default('Upcoming'),
                ])->columns(2),

                // Tab 6: Moderation
                Forms\Components\Tabs\Tab::make('Admin Moderation')->schema([
                    Forms\Components\Textarea::make('admin_review_note')
                        ->label('Internal Admin Review Notes')
                        ->placeholder('Notes regarding verification, physical inspection, or document checks.')
                        ->columnSpanFull(),
                ]),
            ])->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property_code')
                    ->label('Code')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(35)
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable(),
                Tables\Columns\TextColumn::make('locality')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_display')
                    ->label('Price')
                    ->sortable('price'),
                Tables\Columns\TextColumn::make('asset_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Vehicle' => 'info',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_bank_auction')
                    ->label('Auction')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Under Review' => 'warning',
                        'Sold / Rented' => 'gray',
                        'Rejected' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
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
                    ->options([
                        'Surat' => 'Surat',
                        'Ahmedabad' => 'Ahmedabad',
                        'Vadodara' => 'Vadodara',
                        'Rajkot' => 'Rajkot',
                        'Mumbai' => 'Mumbai',
                    ]),
            ])
            ->actions([
                // Quick Assign Property Code Action
                Tables\Actions\Action::make('assignCode')
                    ->label('Assign Code')
                    ->icon('heroicon-o-identification')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('property_code')
                            ->label('Assign Unique Property Code (e.g. 032)')
                            ->required()
                            ->default(fn (Property $record) => $record->property_code),
                    ])
                    ->action(function (Property $record, array $data) {
                        $record->update(['property_code' => $data['property_code']]);
                        Notification::make()
                            ->title('Property Code Assigned')
                            ->body("Code {$data['property_code']} assigned successfully.")
                            ->success()
                            ->send();
                    }),

                // Quick Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Property $record) {
                        $record->update([
                            'status' => 'Active',
                            'is_verified' => true,
                        ]);
                        Notification::make()
                            ->title('Property Approved')
                            ->body('Property is now Active on user feeds.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Property $record) => $record->status !== 'Active'),

                Tables\Actions\EditAction::make(),
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

