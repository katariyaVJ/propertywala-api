<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $table = 'system_config';
    protected $primaryKey = 'config_key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'config_key',
        'config_value',
        'description',
    ];

    public static function get(string $key, $default = null)
    {
        $item = self::find($key);
        return $item ? $item->config_value : $default;
    }
}

