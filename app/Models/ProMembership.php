<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProMembership extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'contact_limit' => 'integer',
            'contacts_used' => 'integer',
            'opt_lock_version' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(ProContactUnlock::class, 'membership_id');
    }

    public function isValid(): bool
    {
        return $this->status === 'Active' &&
            $this->valid_until->isFuture() &&
            $this->contacts_used < $this->contact_limit;
    }

    public function remainingContacts(): int
    {
        return max(0, $this->contact_limit - $this->contacts_used);
    }
}

