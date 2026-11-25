<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const DEFAULT_PLACEHOLDER = 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80';

    protected static function booted(): void
    {
        static::saving(function (PropertyImage $image) {
            $raw = $image->attributes['image_url'] ?? null;
            if (blank($raw)) {
                $original = $image->getRawOriginal('image_url');
                $image->attributes['image_url'] = !blank($original) ? $original : self::DEFAULT_PLACEHOLDER;
            }

            if (blank($image->attributes['thumbnail_url'] ?? null)) {
                $image->attributes['thumbnail_url'] = $image->attributes['image_url'];
            }

            if (blank($image->attributes['label'] ?? null)) {
                $image->attributes['label'] = 'Property Photo';
            }
        });
    }

    public function setImageUrlAttribute($value): void
    {
        $this->attributes['image_url'] = blank($value)
            ? (!blank($this->getRawOriginal('image_url')) ? $this->getRawOriginal('image_url') : self::DEFAULT_PLACEHOLDER)
            : $value;
    }

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'sort_order' => 'integer',
            'file_size_bytes' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getImageUrlAttribute($value): string
    {
        if (empty($value)) {
            return self::DEFAULT_PLACEHOLDER;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }
}
