<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Staff & Lead Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Employees & Staff';

    protected static ?string $modelLabel = 'Employee';

    protected static ?string $pluralModelLabel = 'Employees & Staff';

    /**
     * Auto-generate the next sequential Employee Code (e.g. EMP-01, EMP-02, ...)
     */
    public static function generateNextEmployeeCode(): string
    {
        $codes = User::whereNotNull('employee_code')->pluck('employee_code');
        $maxNum = 0;

        foreach ($codes as $code) {
            if (preg_match('/(?:EMP|STAFF)[-_]?(\d+)/i', (string) $code, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return sprintf('EMP-%02d', $maxNum + 1);
    }

    /**
     * Complete list of admin panel feature permissions
     */
    public static function getAllPermissions(): array
    {
        return [
            'properties.view' => 'Properties: View All Listings & Auctions',
            'properties.create' => 'Properties: Add New Property / Auction Asset',
            'properties.edit' => 'Properties: Edit Property Details',
            'properties.approve_reject' => 'Properties: ⚡ Approve & Publish Under-Review Listings',
            'properties.delete' => 'Properties: Delete Listings (High Risk)',
            'bank_auctions.manage' => 'Bank Auctions: Manage Bank & Vehicle Auctions',
            'new_projects.manage' => 'New Projects: Manage Builder Projects & Units',
            'leads.view_manage' => 'Buyer Leads: View & Contact Buyer Inquiries',
            'experts.manage' => 'Service Directory: Review & Approve Service Experts',
            'users.manage' => 'Staff: Manage Employees & Permissions',
        ];
    }

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
        // Admin / Owner accounts are protected and cannot be deleted
        if (in_array(strtolower($record->role ?? ''), ['admin', 'owner', 'superadmin'])) {
            return false;
        }

        return auth()->user()?->isOwner() ?? false;
    }

    /**
     * Scope query so only Employees and Admins are displayed (normal mobile app users are excluded)
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->whereIn('role', ['Employee', 'employee'])
                      ->orWhereIn('role', ['admin', 'owner', 'superadmin']);
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Employee Credentials & Profile')
                ->description('Create or update staff profile. Credentials are used to log in at /admin.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Employee Full Name')
                        ->placeholder('e.g. Rajesh Patel')
                        ->required()
                        ->maxLength(120),

                    Forms\Components\TextInput::make('employee_code')
                        ->label('Staff ID / Employee Code')
                        ->default(fn () => static::generateNextEmployeeCode())
                        ->required()
                        ->unique(table: 'users', column: 'employee_code', ignoreRecord: true)
                        ->helperText('Auto-generated unique ID for tracking property entries. Editable by Owner.')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('phone')
                        ->label('Mobile Number')
                        ->tel()
                        ->placeholder('e.g. 9876500001')
                        ->required()
                        ->unique(table: 'users', column: 'phone', ignoreRecord: true),

                    Forms\Components\TextInput::make('email')
                        ->label('Email Address (Admin Login ID)')
                        ->email()
                        ->placeholder('e.g. emp1@propertywala.com')
                        ->required()
                        ->unique(table: 'users', column: 'email', ignoreRecord: true)
                        ->helperText('Employee will use this email to log into the admin panel.'),

                    Forms\Components\TextInput::make('password')
                        ->label('Login Password')
                        ->password()
                        ->revealable()
                        ->minLength(6)
                        ->maxLength(100)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->helperText('Required when creating an employee. Leave empty on edit to keep existing password.'),

                    Forms\Components\TextInput::make('city')
                        ->label('Branch / Location City')
                        ->default('Surat')
                        ->maxLength(80),
                ])->columns(2),

            Forms\Components\Section::make('Role & Feature Permissions')
                ->description('Assign which modules and features this employee is allowed to access and manage.')
                ->schema([
                    Forms\Components\Toggle::make('all_access')
                        ->label('⚡ Grant Full Access (All Features)')
                        ->helperText('When enabled, automatically grants all 10 feature permissions.')
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            if ($state) {
                                $set('admin_permissions', array_keys(static::getAllPermissions()));
                            }
                        })
                        ->default(false),

                    Forms\Components\CheckboxList::make('admin_permissions')
                        ->label('Individual Feature Permissions')
                        ->options(static::getAllPermissions())
                        ->columns(2)
                        ->bulkToggleable()
                        ->helperText('Owner always has full system access. Employees will only see enabled modules.'),
                ]),

            Forms\Components\Section::make('Account Status & Controls')
                ->schema([
                    Forms\Components\Toggle::make('is_blocked')
                        ->label('Block Employee Access')
                        ->helperText('When enabled, employee is immediately prevented from logging into the admin panel.')
                        ->default(false),

                    Forms\Components\Textarea::make('block_reason')
                        ->label('Reason for Suspension')
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_blocked'))
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('Staff ID')
                    ->badge()
                    ->color('info')
                    ->placeholder('ADMIN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record): string => (string) ($record->email ?? '')),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Mobile Number')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'admin', 'owner', 'superadmin' => 'danger',
                        'employee' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('access_summary')
                    ->label('Access Permissions')
                    ->state(function (User $record): string {
                        if ($record->isOwner()) {
                            return '⚡ Full Access (Owner)';
                        }
                        $perms = $record->admin_permissions ?? [];
                        $total = count(static::getAllPermissions());
                        if (count($perms) >= $total) {
                            return '⚡ Full Access (All Features)';
                        }
                        if (empty($perms)) {
                            return 'Restricted (0 Features)';
                        }
                        return count($perms) . " of {$total} Features";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, 'Full Access') ? 'success' : 'warning'),

                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('Active Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-no-symbol')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added Date')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('Account Status')
                    ->placeholder('All Staff')
                    ->trueLabel('Blocked Staff Only')
                    ->falseLabel('Active Staff Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->size('sm'),

                Tables\Actions\Action::make('block')
                    ->label('Block')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->button()
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Employee Access')
                    ->modalDescription(fn (User $record) => "Are you sure you want to block {$record->name}? They will be logged out and cannot access the admin panel.")
                    ->action(function (User $record) {
                        $record->update(['is_blocked' => true, 'block_reason' => 'Suspended by Admin']);
                        Notification::make()
                            ->title('Employee Blocked')
                            ->body("{$record->name}'s access has been suspended.")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (User $record) => !$record->is_blocked && !$record->isOwner()),

                Tables\Actions\Action::make('unblock')
                    ->label('Unblock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->action(function (User $record) {
                        $record->update(['is_blocked' => false, 'block_reason' => null]);
                        Notification::make()
                            ->title('Employee Access Restored')
                            ->body("{$record->name} can now log into the admin panel again.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (User $record) => (bool) $record->is_blocked && !$record->isOwner()),

                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->button()
                    ->size('sm')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Employee')
                    ->modalDescription(fn (User $record) => "Are you sure you want to delete employee {$record->name} [{$record->employee_code}]? This action cannot be undone.")
                    ->visible(fn (User $record): bool => !$record->isOwner() && (auth()->user()?->isOwner() ?? false)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
