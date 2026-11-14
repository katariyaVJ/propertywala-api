<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'state',
        'is_popular',
        'display_order',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'display_order' => 'integer',
    ];

    public function localities(): HasMany
    {
        return $this->hasMany(FamousLocality::class);
    }

    public function famousLocalities(): HasMany
    {
        return $this->hasMany(FamousLocality::class);
    }
}
