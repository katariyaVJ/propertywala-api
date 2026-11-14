<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUnit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'carpet_area_sqft' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(NewProject::class, 'project_id');
    }
}

