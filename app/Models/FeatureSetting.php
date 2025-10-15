<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    protected $fillable = ['nama_fitur', 'status'];

    public static function isActive(string $feature): bool
    {
        return self::where('nama_fitur', $feature)->value('status') ?? false;
    }
}
