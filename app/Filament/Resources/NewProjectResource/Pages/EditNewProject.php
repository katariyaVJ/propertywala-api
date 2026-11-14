<?php

namespace App\Filament\Resources\NewProjectResource\Pages;

use App\Filament\Resources\NewProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewProject extends EditRecord
{
    protected static string $resource = NewProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

