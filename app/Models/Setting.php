<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public $timestamps = false;

    public static function get($key, $default = null)
    {
        $s = static::where('key', $key)->first();
        return $s ? $s->value : $default;
    }
}

