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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('leads.view_manage') ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('leads.view_manage') ?? false);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('leads.view_manage') ?? false);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isOwner(); // Deleting leads restricted to Owner
    }

    public static function getNavigationBadge(): ?string
    {
        $newLeads = static::getModel()::where('status', 'New')->count();

        return $newLeads > 0 ? (string) $newLeads : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('property_id')
                ->relationship('property', 'title')
                ->disabled(),
            Forms\Components\TextInput::make('buyer_name')->disabled(),
            Forms\Components\TextInput::make('buyer_phone')->disabled(),
            Forms\Components\TextInput::make('buyer_email')->disabled(),
            Forms\Components\TextInput::make('intent')->disabled(),
            Forms\Components\TextInput::make('timeline')->disabled(),
            Forms\Components\TextInput::make('source')->disabled(),
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
                    ->placeholder('Deleted Property')
                    ->description(fn (BuyerLead $r): string => 'Code: #' . ($r->property?->property_code ?? 'N/A'))
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
            ->defaultSort('created_at', 'desc')
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
                    Tables\Actions\Action::make('markContacted')
                        ->label('Mark as Contacted')
                        ->icon('heroicon-o-phone-arrow-up-right')
                        ->color('warning')
                        ->visible(fn (BuyerLead $r) => $r->status === 'New')
                        ->action(function (BuyerLead $record) {
                            $record->update(['status' => 'Contacted']);
                            \Filament\Notifications\Notification::make()
                                ->title('Lead Contacted')
                                ->body("Lead #{$record->id} marked as contacted.")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('markClosed')
                        ->label('Mark Deal Closed')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (BuyerLead $r) => in_array($r->status, ['New', 'Contacted']))
                        ->action(function (BuyerLead $record) {
                            $record->update(['status' => 'Closed']);
                            \Filament\Notifications\Notification::make()
                                ->title('Lead Closed')
                                ->body("Lead #{$record->id} marked as closed deal.")
                                ->success()
                                ->send();
                        }),
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

