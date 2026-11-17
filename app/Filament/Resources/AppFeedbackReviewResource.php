<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppFeedbackReviewResource\Pages;
use App\Models\AppFeedbackReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppFeedbackReviewResource extends Resource
{
    protected static ?string $model = AppFeedbackReview::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user_phone')->disabled(),
            Forms\Components\TextInput::make('rating')->numeric()->disabled(),
            Forms\Components\TextInput::make('platform')->disabled(),
            Forms\Components\TextInput::make('app_version')->disabled(),
            Forms\Components\Textarea::make('feedback_text')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_phone')->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('feedback_text')->limit(50),
                Tables\Columns\TextColumn::make('platform')->badge(),
                Tables\Columns\TextColumn::make('app_version'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')->options([
                    5 => '5 Stars',
                    4 => '4 Stars',
                    3 => '3 Stars',
                    2 => '2 Stars',
                    1 => '1 Star',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppFeedbackReviews::route('/'),
        ];
    }
}

