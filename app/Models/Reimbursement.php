<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'total',
        'invoice_path',
        'status_1',
        'status_2'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    protected $casts = [
        'date' => 'date',
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
