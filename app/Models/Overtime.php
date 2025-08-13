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
        'status_1',
        'status_2'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function approver()
    {
        return $this->hasOneThrough(
            User::class,       // Tujuan: user leader
            Division::class,   // Perantara: division
            'id',              // PK di divisions
            'id',              // PK di users (leader)
            'employee_id',     // FK di leaves → users.id (employee)
            'leader_id'        // FK di divisions → users.id (leader)
        );
    }
}
