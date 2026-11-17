<?php

namespace App\Filament\Resources\ServiceProfessionalResource\Pages;

use App\Filament\Resources\ServiceProfessionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceProfessionals extends ListRecords
{
    protected static string $resource = ServiceProfessionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Professional')
                ->icon('heroicon-m-user-plus'),
        ];
    }
}

