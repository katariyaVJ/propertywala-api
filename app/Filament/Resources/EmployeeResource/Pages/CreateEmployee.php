<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Enforce or preserve chosen employee role
        $data['role'] = $data['role'] ?? 'Employee';

        // Explicitly ensure password is standard bcrypt/argon hashed
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // If Full Access toggle was checked, grant all feature permissions
        if (!empty($data['all_access'])) {
            $data['admin_permissions'] = array_keys(EmployeeResource::getAllPermissions());
        }

        // Clean up transient form attribute
        unset($data['all_access']);

        $data['is_phone_verified'] = true;
        $data['is_blocked'] = false;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Employee Created Successfully')
            ->body("Staff ID [{$this->record->employee_code}] {$this->record->name} can now log into /admin.");
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
