<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialTravel extends Model
{
    protected $table = 'official_travels';

    protected $fillable = [
        'employee_id',
        'date_start',
        'date_end',
        'total',
        'status_1',
        'status_2'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
    ];
}
