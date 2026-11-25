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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('users.manage') ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('users.manage') ?? false);
    }

    public static function canEdit($record): bool
    {
        if (in_array(strtolower($record->role ?? ''), ['admin', 'owner', 'superadmin'])) {
            return auth()->user()?->isOwner() ?? false;
        }

        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('users.manage') ?? false);
    }

    public static function canDelete($record): bool
    {
        if (in_array(strtolower($record->role ?? ''), ['admin', 'owner', 'superadmin'])) {
            return false;
        }

        return auth()->user()?->isOwner() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account Credentials')
                ->schema([
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
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->label('Password')
                        ->minLength(6)
                        ->maxLength(100)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->helperText('Required when creating a user. Leave empty on edit to keep current password.'),
                    Forms\Components\Select::make('role')
                        ->options([
                            'Buyer / Tenant' => 'Buyer / Tenant',
                            'Property Owner' => 'Property Owner',
                            'Employee' => 'Employee (Staff ID)',
                            'Real Estate Agent' => 'Real Estate Agent',
                            'Builder / Developer' => 'Builder / Developer',
                            'Banker' => 'Bank Officer',
                            'Service Expert' => 'Service Expert',
                            'admin' => 'Administrator / Owner',
                        ])
                        ->default('Buyer / Tenant')
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('employee_code')
                        ->label('Staff ID / Employee Code')
                        ->placeholder('e.g. EMP-01, EMP-02')
                        ->maxLength(50)
                        ->visible(fn (Forms\Get $get): bool => $get('role') === 'Employee'),
                    Forms\Components\TextInput::make('city')
                        ->default('Surat'),
                ])->columns(2),

            Forms\Components\Section::make('Employee Access & Permissions')
                ->description('Select the exact modules and actions this employee is allowed to perform in the admin panel.')
                ->visible(fn (Forms\Get $get): bool => $get('role') === 'Employee' && (auth()->user()?->isOwner() ?? false))
                ->schema([
                    Forms\Components\CheckboxList::make('admin_permissions')
                        ->label('Granted Permissions')
                        ->options([
                            'properties.view' => 'View Properties List',
                            'properties.create' => 'Add New Property / Asset',
                            'properties.edit' => 'Edit Property Details',
                            'properties.approve_reject' => '⚡ Approve & Reject Under-Review Properties',
                            'properties.delete' => 'Delete Property (High Risk)',
                            'bank_auctions.manage' => 'Manage Bank Auction & Vehicle Assets',
                            'new_projects.manage' => 'Manage Builder New Projects',
                            'leads.view_manage' => 'View & Manage Buyer Leads',
                            'experts.manage' => 'Manage Service Professionals',
                            'subscriptions.view' => 'View Subscriptions & Revenue',
                            'users.manage' => 'Manage Users & Customers',
                        ])
                        ->columns(2)
                        ->bulkToggleable()
                        ->helperText('Owner always has 100% full access. Employees will only see enabled modules.'),
                ]),

            Forms\Components\Section::make('Account Status & Controls')
                ->schema([
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
                        ->visible(fn (Forms\Get $get) => $get('is_blocked'))
                        ->columnSpanFull(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record): ?string => ($record->employee_code ? '[' . $record->employee_code . '] ' : '') . ($record->email ?? '')),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Mobile')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'admin', 'superadmin' => 'danger',
                        'employee' => 'primary',
                        'builder', 'builder / developer' => 'warning',
                        'banker' => 'info',
                        'service expert', 'professional' => 'primary',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('Staff ID')
                    ->badge()
                    ->color('info')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_phone_verified')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('Blocked')
                    ->boolean()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')->options([
                    'Buyer / Tenant' => 'Buyer / Tenant',
                    'Property Owner' => 'Property Owner',
                    'Real Estate Agent' => 'Real Estate Agent',
                    'Builder / Developer' => 'Builder / Developer',
                    'Employee' => 'Employee (Staff ID)',
                    'Banker' => 'Bank Officer',
                    'Service Expert' => 'Service Expert',
                    'owner' => 'Owner (Legacy)',
                    'agent' => 'Agent (Legacy)',
                    'admin' => 'Administrator',
                ]),
                Tables\Filters\TernaryFilter::make('is_blocked')->label('Blocked Users'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('block')
                        ->label('Block User')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (User $record) {
                            $record->update(['is_blocked' => true, 'block_reason' => 'Blocked by Admin']);
                        })
                        ->visible(fn (User $record) => !$record->is_blocked && $record->role !== 'admin'),
                    Tables\Actions\Action::make('unblock')
                        ->label('Unblock User')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (User $record) {
                            $record->update(['is_blocked' => false, 'block_reason' => null]);
                        })
                        ->visible(fn (User $record) => (bool) $record->is_blocked),
                    Tables\Actions\EditAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

