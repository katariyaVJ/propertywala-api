<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuyerLeadResource\Pages;
use App\Models\BuyerLead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BuyerLeadResource extends Resource
{
    protected static ?string $model = BuyerLead::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'User & Lead Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('property_id')
                ->relationship('property', 'title')
                ->disabled(),
            Forms\Components\TextInput::make('buyer_name')->disabled(),
            Forms\Components\TextInput::make('buyer_phone')->disabled(),
            Forms\Components\TextInput::make('intent')->disabled(),
            Forms\Components\TextInput::make('timeline')->disabled(),
            Forms\Components\Select::make('status')
                ->options([
                    'New' => 'New',
                    'Contacted' => 'Contacted',
                    'Closed' => 'Closed',
                    'Spam' => 'Spam',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('buyer_name')
                    ->label('Buyer / Lead')
                    ->description(fn (BuyerLead $r): string => $r->buyer_phone)
                    ->searchable(['buyer_name', 'buyer_phone'])
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('property.title')
                    ->label('Property Inquired')
                    ->description(fn (BuyerLead $r): string => 'Code: #' . ($r->property->property_code ?? 'N/A'))
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('intent')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'info',
                        'Contacted' => 'warning',
                        'Closed' => 'success',
                        'Spam' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('timeline')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'New' => 'New',
                        'Contacted' => 'Contacted',
                        'Closed' => 'Closed',
                        'Spam' => 'Spam',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuyerLeads::route('/'),
        ];
    }
}

