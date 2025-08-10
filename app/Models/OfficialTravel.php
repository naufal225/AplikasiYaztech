<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialTravel extends Model
{
    protected $table = 'official_travels';

    protected $fillable = [
        'employee_id',
        'approver_id',
        'date_start',
        'date_end',
        'total',
        'status'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approver_id');
    }

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
    ];
}
