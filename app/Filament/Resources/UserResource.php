<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User & Lead Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(120),
            Forms\Components\TextInput::make('phone')
                ->tel()
                ->label('Mobile Number')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('alt_phone')
                ->tel()
                ->label('Alternate Mobile'),
            Forms\Components\TextInput::make('email')
                ->email()
                ->label('Email Address')
                ->unique(ignoreRecord: true),
            Forms\Components\Select::make('role')
                ->options([
                    'Buyer / Tenant' => 'Buyer / Tenant',
                    'Property Owner' => 'Property Owner',
                    'Real Estate Agent' => 'Real Estate Agent',
                    'Builder / Developer' => 'Builder / Developer',
                    'Banker' => 'Bank Officer',
                    'Service Expert' => 'Service Expert',
                    'admin' => 'Administrator',
                ])
                ->default('Buyer / Tenant')
                ->required(),
            Forms\Components\TextInput::make('city')
                ->default('Surat'),
            Forms\Components\Toggle::make('notification_updates')
                ->label('In-App Notifications Enabled')
                ->default(true),
            Forms\Components\Toggle::make('is_phone_verified')
                ->label('Phone Verified')
                ->default(true),
            Forms\Components\Toggle::make('is_blocked')
                ->label('Block User')
                ->default(false),
            Forms\Components\Textarea::make('block_reason')
                ->visible(fn (Forms\Get $get) => $get('is_blocked')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Mobile')->searchable(),
                Tables\Columns\TextColumn::make('alt_phone')->label('Alt Mobile')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'builder' => 'warning',
                        'banker' => 'info',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('city')->sortable(),
                Tables\Columns\IconColumn::make('is_phone_verified')->boolean(),
                Tables\Columns\IconColumn::make('is_blocked')->boolean()->color('danger'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->options([
                    'user' => 'User',
                    'owner' => 'Owner',
                    'agent' => 'Agent',
                    'builder' => 'Builder',
                    'banker' => 'Banker',
                    'professional' => 'Professional',
                    'admin' => 'Admin',
                ]),
                Tables\Filters\TernaryFilter::make('is_blocked')->label('Blocked Users'),
            ])
            ->actions([
                Tables\Actions\Action::make('block')
                    ->label('Block')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['is_blocked' => true, 'block_reason' => 'Blocked by Admin']);
                    })
                    ->visible(fn (User $record) => !$record->is_blocked && $record->role !== 'admin'),
                Tables\Actions\Action::make('unblock')
                    ->label('Unblock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (User $record) {
                        $record->update(['is_blocked' => false, 'block_reason' => null]);
                    })
                    ->visible(fn (User $record) => (bool) $record->is_blocked),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

