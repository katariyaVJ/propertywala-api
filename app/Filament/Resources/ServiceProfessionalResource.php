<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProfessionalResource\Pages;
use App\Models\ServiceProfessional;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceProfessionalResource extends Resource
{
    protected static ?string $model = ServiceProfessional::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Services & Expert Directory';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('experts.manage') ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('experts.manage') ?? false);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isOwner() || (auth()->user()?->hasAdminPermission('experts.manage') ?? false);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isOwner();
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'Pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Linked Account User')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => auth()->id())
                ->required(),
            Forms\Components\Select::make('category')
                ->options([
                    'Legal Title Verification' => 'Legal Title Verification',
                    'Lawyer / Advocate' => 'Lawyer / Advocate',
                    'Banker (for Loan)' => 'Banker (for Loan)',
                    'Interior & Vastu' => 'Interior & Vastu',
                ])
                ->required(),
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('firm_name')->required()->maxLength(150),
            Forms\Components\TextInput::make('phone')->tel()->required()->maxLength(20),
            Forms\Components\TextInput::make('email')->email()->maxLength(160),
            Forms\Components\TextInput::make('city')->default('Surat')->required()->maxLength(80),
            Forms\Components\TextInput::make('experience_years')->numeric()->default(3),
            Forms\Components\TextInput::make('license_or_reg_number')->maxLength(100),
            Forms\Components\Select::make('status')
                ->options([
                    'Pending' => 'Pending Review',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                ])
                ->default('Pending')
                ->live()
                ->required(),
            Forms\Components\TextInput::make('rating')->numeric()->default(4.9),
            Forms\Components\TagsInput::make('specializations'),
            Forms\Components\Textarea::make('description')
                ->label('Bio / Experience Summary')
                ->columnSpanFull(),
            Forms\Components\Textarea::make('rejection_reason')
                ->label('Rejection Reason (Visible to Applicant)')
                ->visible(fn (Forms\Get $get) => $get('status') === 'Rejected')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Expert Name')
                    ->description(fn (ServiceProfessional $r): string => $r->firm_name)
                    ->searchable(['name', 'firm_name'])
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Legal Title Verification' => 'warning',
                        'Lawyer / Advocate' => 'primary',
                        'Banker (for Loan)' => 'info',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('city')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        'Rejected' => 'danger',
                        default => 'secondary',
                    })
                    ->description(fn (ServiceProfessional $r): ?string => $r->status === 'Rejected' ? ($r->rejection_reason ?? $r->description) : null),
                Tables\Columns\TextColumn::make('rating')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    'Legal Title Verification' => 'Legal Title Verification',
                    'Lawyer / Advocate' => 'Lawyer / Advocate',
                    'Banker (for Loan)' => 'Banker (for Loan)',
                    'Interior & Vastu' => 'Interior & Vastu',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    'Approved' => 'Approved',
                    'Pending' => 'Pending',
                    'Rejected' => 'Rejected',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Accept & Approve Professional')
                    ->modalDescription(fn (ServiceProfessional $record) => "Are you sure you want to approve {$record->name} ({$record->firm_name})? They will immediately be live in the app.")
                    ->action(function (ServiceProfessional $record) {
                        $record->update([
                            'status' => 'Approved',
                            'rejection_reason' => null,
                        ]);
                        Notification::make()
                            ->title('Professional Approved')
                            ->body("{$record->name} is now live in the mobile app directory.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ServiceProfessional $record) => $record->status !== 'Approved'),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->placeholder('e.g. License verification unconfirmed, or unable to reach mobile number.')
                            ->required(),
                    ])
                    ->action(function (ServiceProfessional $record, array $data) {
                        $record->update([
                            'status' => 'Rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()
                            ->title('Application Rejected')
                            ->body("{$record->name} has been rejected with reason.")
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (ServiceProfessional $record) => $record->status !== 'Rejected'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn () => auth()->user()?->isOwner() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceProfessionals::route('/'),
            'create' => Pages\CreateServiceProfessional::route('/create'),
            'edit' => Pages\EditServiceProfessional::route('/{record}/edit'),
        ];
    }
}

