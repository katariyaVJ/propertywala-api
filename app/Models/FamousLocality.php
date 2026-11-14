<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamousLocality extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'subtitle',
        'avg_price_per_sqft',
        'is_famous',
    ];

    protected $casts = [
        'avg_price_per_sqft' => 'decimal:2',
        'is_famous' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}

