<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Property Listing')
                ->modalDescription(fn () => "Are you sure you want to delete property #{$this->record->property_code} - {$this->record->title}? This action cannot be undone.")
                ->visible(fn (): bool => PropertyResource::canDelete($this->record)),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $predefined = [
            // Residential
            'Apartment / Flat',
            'Row House / Tenement',
            'Bungalow / Villa',
            'Farmhouse',
            'Penthouse',
            'Studio Apartment / 1 RK',
            'Duplex',
            'Residential Plot / NA',
            'Residential Plot / NA Land',
            // Commercial
            'Commercial Shop',
            'Office Space',
            'Showroom',
            'Commercial Showroom',
            'Warehouse / Godown',
            'Industrial / Factory',
            'Industrial / Factory Shed',
            'Commercial Land / Plot',
            'Co-working Space',
            'Hotel / Resort / Banquet',
            // Agricultural
            'Agricultural Land',
            'Agricultural Land / Farm',
            // Vehicle
            'Small Commercial Vehicle',
            '3 Wheeler',
            '2 Wheeler',
            'Passenger Car / Hatchback / Sedan',
            'SUV / MUV',
            'Commercial Truck',
            'Bus / Tempo Traveller',
            'Construction / JCB / Crane',
            'Tractor / Agro Vehicle',
            'Electric Vehicle (EV)',
            'Luxury Car',
            'Car / SUV',
            'Bus / Van',
            'Construction / JCB',
            'Tractor / Agro',
            'Two Wheeler',
        ];

        if (!empty($data['property_sub_type']) && !in_array($data['property_sub_type'], $predefined, true)) {
            $data['custom_category'] = $data['property_sub_type'];
            $data['property_sub_type'] = 'Other';
        }

        // Sync vehicle_category on edit if missing
        if (($data['asset_type'] ?? '') === 'Vehicle' && empty($data['vehicle_category']) && !empty($data['property_sub_type'])) {
            $data['vehicle_category'] = $data['property_sub_type'];
        }

        $predefinedBrands = [
            'Ashok Leyland', 'Tata Motors', 'Mahindra & Mahindra', 'Maruti Suzuki',
            'Hyundai', 'Toyota', 'Eicher', 'BharatBenz', 'Force Motors', 'Isuzu',
            'Honda', 'Hero MotoCorp', 'Bajaj Auto', 'TVS Motor', 'JCB', 'Other Brand',
        ];
        if (!empty($data['vehicle_brand']) && !in_array($data['vehicle_brand'], $predefinedBrands, true)) {
            $data['custom_vehicle_brand'] = $data['vehicle_brand'];
            $data['vehicle_brand'] = 'Other Brand';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Track reviewer if status changed
        if (isset($data['status'])) {
            $currentStatus = $this->record->status;
            if ($data['status'] !== $currentStatus) {
                $data['reviewed_by_user_id'] = auth()->id();
            }
        }

        // Ensure user_id is never lost
        if (empty($data['user_id'])) {
            $data['user_id'] = $this->record->user_id ?? auth()->id() ?? 1;
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
