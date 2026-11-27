<?php

namespace App\Filament\Resources\ProMembershipResource\Pages;

use App\Filament\Resources\ProMembershipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProMembership extends EditRecord
{
    protected static string $resource = ProMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isOwner() ?? false),
        ];
    }
}
