<?php

namespace App\Filament\Resources\PushCampaignResource\Pages;

use App\Filament\Resources\PushCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePushCampaign extends CreateRecord
{
    protected static string $resource = PushCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id() ?? 1;
        $data['sent_at'] = now();
        $data['status'] = 'Completed';
        return $data;
    }
}

