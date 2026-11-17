<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PushCampaignResource\Pages;
use App\Models\PushCampaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PushCampaignResource extends Resource
{
    protected static ?string $model = PushCampaign::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(160),
            Forms\Components\Select::make('category')
                ->options([
                    'System Alert' => 'System Alert',
                    'Price Drop' => 'Price Drop',
                    'Bank Auction' => 'Bank Auction Alert',
                    'Offer' => 'Pro Plan Offer',
                ])
                ->default('System Alert'),
            Forms\Components\Select::make('target_audience')
                ->options([
                    'All Users' => 'All Users',
                    'Pro Users' => 'Pro Users Only',
                    'City Specific' => 'City Specific',
                ])
                ->default('All Users'),
            Forms\Components\TextInput::make('target_city')->placeholder('Surat'),
            Forms\Components\TextInput::make('deep_link_url')->placeholder('propertywala://property/032'),
            Forms\Components\TextInput::make('banner_image_url')->url(),
            Forms\Components\Textarea::make('body')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('target_audience'),
                Tables\Columns\TextColumn::make('target_city'),
                Tables\Columns\TextColumn::make('total_recipients')->label('Delivered'),
                Tables\Columns\TextColumn::make('status')->badge()->color('success'),
                Tables\Columns\TextColumn::make('sent_at')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPushCampaigns::route('/'),
            'create' => Pages\CreatePushCampaign::route('/create'),
        ];
    }
}

