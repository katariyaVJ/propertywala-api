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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['experience_years'])) {
            $years = (int) $data['experience_years'];
            $data['experience_display'] = "{$years}+ Years Experience";
        }

        return $data;
    }
}
