<?php

namespace App\Filament\Resources\BuyerLeadResource\Pages;

use App\Filament\Resources\BuyerLeadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuyerLead extends EditRecord
{
    protected static string $resource = BuyerLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isOwner() ?? false),
        ];
    }
}
