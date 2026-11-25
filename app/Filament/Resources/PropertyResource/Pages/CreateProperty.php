<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProperty extends CreateRecord
{
    protected static string $resource = PropertyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-assign user_id if not present
        if (empty($data['user_id'])) {
            $data['user_id'] = auth()->id() ?? 1;
        }

        // Track employee/admin creator
        $data['created_by_user_id'] = auth()->id();

        // Enforce employee role when staff creates record
        if (auth()->user()?->isEmployee()) {
            $data['posted_by_role'] = 'Employee';
        }

        // Map custom category if 'Other' was selected
        if (($data['property_sub_type'] ?? '') === 'Other' && !empty($data['custom_category'])) {
            $data['property_sub_type'] = trim($data['custom_category']);
        }
        unset($data['custom_category']);

        // Map custom vehicle brand if 'Other Brand' was selected
        if (($data['vehicle_brand'] ?? '') === 'Other Brand' && !empty($data['custom_vehicle_brand'])) {
            $data['vehicle_brand'] = trim($data['custom_vehicle_brand']);
        }
        unset($data['custom_vehicle_brand']);

        // Sync vehicle title & description if vehicle
        if (($data['asset_type'] ?? '') === 'Vehicle') {
            if (empty($data['title']) && !empty($data['vehicle_title'])) {
                $data['title'] = $data['vehicle_title'];
            }
            if (empty($data['vehicle_title']) && !empty($data['title'])) {
                $data['vehicle_title'] = $data['title'];
            }
            if (empty($data['description']) && !empty($data['vehicle_description'])) {
                $data['description'] = $data['vehicle_description'];
            }
            if (empty($data['vehicle_description']) && !empty($data['description'])) {
                $data['vehicle_description'] = $data['description'];
            }
        }

        return $data;
    }
}
