<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'employee_id',
        'date_start',
        'date_end',
        'reason',
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
