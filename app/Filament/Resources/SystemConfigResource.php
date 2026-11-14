<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemConfigResource\Pages;
use App\Models\SystemConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemConfigResource extends Resource
{
    protected static ?string $model = SystemConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('config_key')
                ->label('Setting Key')
                ->disabled(fn ($context) => $context === 'edit')
                ->required()
                ->maxLength(80),
            Forms\Components\TextInput::make('description')
                ->maxLength(255),
            Forms\Components\Textarea::make('config_value')
                ->label('Setting Value')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('config_key')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('config_value')->limit(50),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemConfigs::route('/'),
            'create' => Pages\CreateSystemConfig::route('/create'),
            'edit' => Pages\EditSystemConfig::route('/{record}/edit'),
        ];
    }
}

