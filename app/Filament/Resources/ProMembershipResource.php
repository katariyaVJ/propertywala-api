<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProMembershipResource\Pages;
use App\Models\ProMembership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProMembershipResource extends Resource
{
    protected static ?string $model = ProMembership::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Subscriptions & Revenue';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->disabled(),
            Forms\Components\TextInput::make('plan_name')->required(),
            Forms\Components\TextInput::make('price_paid')->numeric()->prefix('₹'),
            Forms\Components\TextInput::make('contact_limit')->numeric()->default(40),
            Forms\Components\TextInput::make('contacts_used')->numeric()->default(0),
            Forms\Components\DateTimePicker::make('valid_from'),
            Forms\Components\DateTimePicker::make('valid_until'),
            Forms\Components\Select::make('status')
                ->options([
                    'Active' => 'Active',
                    'Expired' => 'Expired',
                    'Cancelled' => 'Cancelled',
                ])
                ->default('Active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('user.phone')->searchable(),
                Tables\Columns\TextColumn::make('plan_name')->badge(),
                Tables\Columns\TextColumn::make('price_paid')->money('INR'),
                Tables\Columns\TextColumn::make('contacts_used')
                    ->label('Quota Used')
                    ->formatStateUsing(fn ($record) => "{$record->contacts_used} / {$record->contact_limit}"),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Expired' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('valid_until')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'Active' => 'Active',
                    'Expired' => 'Expired',
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProMemberships::route('/'),
        ];
    }
}

