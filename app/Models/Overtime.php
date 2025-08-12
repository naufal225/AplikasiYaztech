<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $fillable = [
        'employee_id',
        'date_start',
        'date_end',
        'total',
        'status'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];
}
