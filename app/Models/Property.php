<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'reserve_price' => 'decimal:2',
            'emd_amount' => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'area_value' => 'decimal:2',
            'super_area_value' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'is_bank_auction' => 'boolean',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'is_deleted' => 'boolean',
            'auction_timestamp' => 'datetime',
            'emd_last_timestamp' => 'datetime',
            'sold_date' => 'date',
            'featured_until' => 'date',
            'amenities' => 'array',
            'vehicle_inspection_features' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order', 'asc');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->orderByRaw('is_cover DESC, sort_order ASC, id ASC');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(BuyerLead::class);
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(ProContactUnlock::class);
    }

    public function savedByUsers(): HasMany
    {
        return $this->hasMany(SavedProperty::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PropertyReport::class);
    }

    /**
     * Scope for active approved properties displayed on user feeds
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active')->where('is_deleted', false);
    }

    /**
     * Scope for real estate vs seized vehicles
     */
    public function scopePropertiesOnly($query)
    {
        return $query->where('asset_type', 'Property');
    }

    public function scopeVehiclesOnly($query)
    {
        return $query->where('asset_type', 'Vehicle');
    }

    public function scopeBankAuctions($query)
    {
        return $query->where('is_bank_auction', true);
    }
}

