<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_blocked || $this->trashed()) {
            return false;
        }

        $role = strtolower((string) $this->role);

        // Owner / Administrator has full panel access
        if (in_array($role, ['admin', 'superadmin', 'super_admin', 'owner'], true)) {
            return true;
        }

        // Employee / Staff roles can access panel
        if (in_array($role, ['employee', 'staff', 'manager', 'telecaller', 'field executive'], true)) {
            return true;
        }

        return false;
    }

    public function isOwner(): bool
    {
        return in_array(strtolower((string) $this->role), ['admin', 'superadmin', 'super_admin', 'owner'], true);
    }

    public function isEmployee(): bool
    {
        return in_array(strtolower((string) $this->role), ['employee', 'staff', 'manager', 'telecaller', 'field executive'], true);
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        $permissions = (array) ($this->admin_permissions ?? []);
        return in_array($permission, $permissions, true);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'banned_at' => 'datetime',
            'notification_updates' => 'boolean',
            'is_phone_verified' => 'boolean',
            'is_blocked' => 'boolean',
            'is_deleted' => 'boolean',
            'admin_permissions' => 'array',
            'password' => 'hashed',
        ];
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function proMemberships(): HasMany
    {
        return $this->hasMany(ProMembership::class);
    }

    public function contactUnlocks(): HasMany
    {
        return $this->hasMany(ProContactUnlock::class);
    }

    public function buyerLeads(): HasMany
    {
        return $this->hasMany(BuyerLead::class, 'buyer_user_id');
    }

    public function sellerLeads(): HasMany
    {
        return $this->hasMany(BuyerLead::class, 'seller_user_id');
    }

    public function savedProperties(): HasMany
    {
        return $this->hasMany(SavedProperty::class);
    }

    public function serviceProfile(): HasMany
    {
        return $this->hasMany(ServiceProfessional::class);
    }
}
