<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $perms = $data['admin_permissions'] ?? [];
        $total = count(EmployeeResource::getAllPermissions());

        $data['all_access'] = is_array($perms) && count($perms) >= $total;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If Full Access toggle is enabled, set all permissions
        if (!empty($data['all_access'])) {
            $data['admin_permissions'] = array_keys(EmployeeResource::getAllPermissions());
        }

        // Clean up transient form attribute
        unset($data['all_access']);

        // Explicitly hash password if a new one was provided, otherwise ignore it
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Employee Updated')
            ->body("Staff profile for {$this->record->name} has been updated.");
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => !in_array(strtolower($this->record->role ?? ''), ['admin', 'owner', 'superadmin']) && (auth()->user()?->isOwner() ?? false)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
