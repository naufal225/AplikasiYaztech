<?php

namespace App\Models;

use App\HasDualStatus;
use Illuminate\Database\Eloquent\Model;

class OfficialTravel extends Model
{
    use HasDualStatus;
    protected $table = 'official_travels';

    protected $fillable = [
        'employee_id',
        'date_start',
        'date_end',
        'total',
        'status_1',
        'status_2',
        'note_1',
        'note_2',
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

    public function getApproverAttribute()
    {
        return $this->employee?->division?->leader; // bisa null-safe
    }
}
