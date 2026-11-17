<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProfessionalResource\Pages;
use App\Models\ServiceProfessional;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceProfessionalResource extends Resource
{
    protected static ?string $model = ServiceProfessional::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Services & Expert Directory';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category')
                ->options([
                    'Legal Title Verification' => 'Legal Title Verification',
                    'Banker (for Loan)' => 'Banker (for Loan)',
                    'Interior & Vastu' => 'Interior & Vastu',
                ])
                ->required(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('firm_name')->required(),
            Forms\Components\TextInput::make('phone')->tel()->required(),
            Forms\Components\TextInput::make('city')->default('Surat')->required(),
            Forms\Components\TextInput::make('experience_years')->numeric()->default(3),
            Forms\Components\TextInput::make('license_or_reg_number'),
            Forms\Components\Select::make('status')
                ->options([
                    'Pending' => 'Pending Review',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                ])
                ->default('Pending')
                ->required(),
            Forms\Components\TextInput::make('rating')->numeric()->default(4.9),
            Forms\Components\TagsInput::make('specializations'),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
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
                    }),
                Tables\Columns\TextColumn::make('rating')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    'Legal Title Verification' => 'Legal Title Verification',
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (ServiceProfessional $record) {
                            $record->update(['status' => 'Approved']);
                        })
                        ->visible(fn (ServiceProfessional $record) => $record->status !== 'Approved'),
                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (ServiceProfessional $record) {
                            $record->update(['status' => 'Rejected']);
                        })
                        ->visible(fn (ServiceProfessional $record) => $record->status === 'Pending'),
                    Tables\Actions\EditAction::make(),
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

