<?php

namespace App\Filament\Resources\PushCampaignResource\Pages;

use App\Filament\Resources\PushCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPushCampaigns extends ListRecords
{
    protected static string $resource = PushCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

