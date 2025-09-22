<?php

namespace App\Models;

use App\Traits\HasDualStatus;
use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    use HasDualStatus;
    protected $fillable = [
        'employee_id',
        'date',
        'total',
        'invoice_path',
        'status_1',
        'status_2',
        'note_1',
        'note_2',
        'marked_down',
        'locked_by',
        'locked_at',
        'reimbursement_type_id',
        'approved_date',
        'rejected_date'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    protected $casts = [
        'date' => 'date',
        'marked_down' => 'boolean',
        'locked_at' => 'datetime',
        'employee_id' => 'integer',
        'approved_date' => 'datetime',
        'rejected_date' => 'datetime',
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

    public function type()
    {
        return $this->belongsTo(ReimbursementType::class, 'reimbursement_type_id');
    }
}
