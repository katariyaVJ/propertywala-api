<?php

namespace App\Filament\Resources\ServiceProfessionalResource\Pages;

use App\Filament\Resources\ServiceProfessionalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceProfessional extends CreateRecord
{
    protected static string $resource = ServiceProfessionalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $years = (int) ($data['experience_years'] ?? 3);
        $data['experience_display'] = "{$years}+ Years Experience";

        return $data;
    }
}
