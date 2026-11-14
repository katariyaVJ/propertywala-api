<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewProject extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'tags' => 'array',
            'amenities' => 'array',
        ];
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProjectUnit::class, 'project_id');
    }
}

