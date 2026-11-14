<?php

namespace App\Filament\Resources\ServiceProfessionalResource\Pages;

use App\Filament\Resources\ServiceProfessionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceProfessional extends EditRecord
{
    protected static string $resource = ServiceProfessionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

