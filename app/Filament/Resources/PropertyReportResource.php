<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyReportResource\Pages;
use App\Models\PropertyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyReportResource extends Resource
{
    protected static ?string $model = PropertyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Moderation & Safety';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('property_id')
                ->relationship('property', 'title')
                ->disabled(),
            Forms\Components\TextInput::make('reason')->disabled(),
            Forms\Components\Textarea::make('details')->disabled(),
            Forms\Components\Select::make('status')
                ->options([
                    'Pending' => 'Pending Investigation',
                    'Investigated' => 'Investigated (Valid)',
                    'Dismissed' => 'Dismissed',
                    'Delisted' => 'Delisted / Blocked',
                ])
                ->required(),
            Forms\Components\Textarea::make('admin_action_taken')
                ->placeholder('Action taken details (e.g. Seller warned, property marked as sold)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.title')->limit(35)->searchable(),
                Tables\Columns\TextColumn::make('reason')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('details')->limit(40),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'danger',
                        'Investigated' => 'warning',
                        'Dismissed' => 'gray',
                        'Delisted' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'Pending' => 'Pending',
                    'Investigated' => 'Investigated',
                    'Dismissed' => 'Dismissed',
                    'Delisted' => 'Delisted',
                ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('delist')
                        ->label('Delist Property')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (PropertyReport $record) {
                            $record->property?->update(['status' => 'Rejected', 'is_deleted' => true]);
                            $record->update(['status' => 'Delisted', 'admin_action_taken' => 'Property delisted due to verified report']);
                        })
                        ->visible(fn (PropertyReport $record) => $record->status === 'Pending'),
                    Tables\Actions\Action::make('dismiss')
                        ->label('Dismiss')
                        ->icon('heroicon-o-check')
                        ->color('gray')
                        ->action(function (PropertyReport $record) {
                            $record->update(['status' => 'Dismissed']);
                        })
                        ->visible(fn (PropertyReport $record) => $record->status === 'Pending'),
                    Tables\Actions\EditAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPropertyReports::route('/'),
        ];
    }
}

