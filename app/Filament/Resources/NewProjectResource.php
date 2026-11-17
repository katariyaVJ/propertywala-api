<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewProjectResource\Pages;
use App\Models\NewProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewProjectResource extends Resource
{
    protected static ?string $model = NewProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Real Estate & Bank Auctions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(160),
            Forms\Components\TextInput::make('developer_name')->required()->maxLength(140),
            Forms\Components\TextInput::make('developer_stats')->maxLength(100),
            Forms\Components\TextInput::make('rera_number')->required()->unique(ignoreRecord: true)->maxLength(80),
            Forms\Components\TextInput::make('locality')->required()->maxLength(160),
            Forms\Components\TextInput::make('city')->default('Surat')->required(),
            Forms\Components\TextInput::make('price_range')->placeholder('₹ 1.25 Cr - 2.80 Cr')->required(),
            Forms\Components\TextInput::make('bhk_options')->placeholder('3, 4 & 5 BHK')->required(),
            Forms\Components\TextInput::make('possession')->placeholder('Dec 2027')->required(),
            Forms\Components\TextInput::make('construction_stage')->placeholder('Under Construction (7th Slab)'),
            Forms\Components\TextInput::make('total_units')->placeholder('160 Units across 4 Towers'),
            Forms\Components\TextInput::make('brochure_url')->url()->maxLength(500),
            Forms\Components\Toggle::make('is_featured')->default(true),
            Forms\Components\TagsInput::make('tags')->placeholder('RERA Approved, Luxury Enclave'),
            Forms\Components\TagsInput::make('amenities')->placeholder('Infinity Pool, Gymnasium'),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project / Developer')
                    ->description(fn (NewProject $record): string => $record->developer_name . ' • RERA: ' . $record->rera_number)
                    ->searchable(['name', 'developer_name', 'rera_number'])
                    ->weight('bold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('locality')
                    ->label('Location')
                    ->description(fn (NewProject $record): string => $record->city)
                    ->searchable(['locality', 'city']),
                Tables\Columns\TextColumn::make('price_range')
                    ->label('Price Range')
                    ->description(fn (NewProject $record): ?string => $record->bhk_options),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')->options([
                    'Surat' => 'Surat',
                    'Ahmedabad' => 'Ahmedabad',
                    'Vadodara' => 'Vadodara',
                ]),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured Only'),
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
            'index' => Pages\ListNewProjects::route('/'),
            'create' => Pages\CreateNewProject::route('/create'),
            'edit' => Pages\EditNewProject::route('/{record}/edit'),
        ];
    }
}

