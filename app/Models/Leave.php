<?php

namespace App\Models;

use App\HasDualStatus;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasDualStatus;
    protected $fillable = [
        'employee_id',
        'date_start',
        'date_end',
        'reason',
        'status_1',
        'note_1',
    ];

    protected function finalStatusColumns(): array
    {
        return ['status_1'];
    }


    public function employee()
    {
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
