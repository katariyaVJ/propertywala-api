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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('subscriptions.view') ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('subscriptions.view') ?? false);
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
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('extend30Days')
                        ->label('+30 Days Extension')
                        ->icon('heroicon-o-clock')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (ProMembership $r) => auth()->user()?->isOwner())
                        ->action(function (ProMembership $record) {
                            $baseDate = $record->valid_until && \Carbon\Carbon::parse($record->valid_until)->isFuture()
                                ? \Carbon\Carbon::parse($record->valid_until)
                                : \Carbon\Carbon::now();
                            $newDate = $baseDate->addDays(30);
                            $record->update([
                                'valid_until' => $newDate,
                                'status' => 'Active',
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Membership Extended')
                                ->body("{$record->plan_name} extended for 30 days until {$newDate->format('d M Y')}.")
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
            'index' => Pages\ListProMemberships::route('/'),
        ];
    }
}

