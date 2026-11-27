<?php

namespace App\Filament\Resources\PropertyReportResource\Pages;

use App\Filament\Resources\PropertyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPropertyReport extends EditRecord
{
    protected static string $resource = PropertyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isOwner() ?? false),
        ];
    }
}
