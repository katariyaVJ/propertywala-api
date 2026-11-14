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

    protected static ?int $navigationSort = 3;

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
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('developer_name')->searchable(),
                Tables\Columns\TextColumn::make('city')->sortable(),
                Tables\Columns\TextColumn::make('locality')->searchable(),
                Tables\Columns\TextColumn::make('price_range'),
                Tables\Columns\TextColumn::make('rera_number')->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')->options([
                    'Surat' => 'Surat',
                    'Ahmedabad' => 'Ahmedabad',
                    'Vadodara' => 'Vadodara',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

