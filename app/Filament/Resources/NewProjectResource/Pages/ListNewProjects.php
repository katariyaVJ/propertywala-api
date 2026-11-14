<?php

namespace App\Filament\Resources\NewProjectResource\Pages;

use App\Filament\Resources\NewProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewProjects extends ListRecords
{
    protected static string $resource = NewProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

