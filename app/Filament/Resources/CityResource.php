<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Real Estate & Bank Auctions';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isOwner();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isOwner();
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isOwner();
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isOwner();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->unique(ignoreRecord: true)->maxLength(80),
            Forms\Components\TextInput::make('state')->default('Gujarat')->required()->maxLength(80),
            Forms\Components\TextInput::make('display_order')->numeric()->default(1),
            Forms\Components\Toggle::make('is_popular')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('state')->sortable(),
                Tables\Columns\IconColumn::make('is_popular')->boolean(),
                Tables\Columns\TextColumn::make('display_order')->sortable(),
                Tables\Columns\TextColumn::make('localities_count')
                    ->counts('localities')
                    ->label('Localities'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}

