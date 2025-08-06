<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    protected $fillable = [
        'employee_id',
        'approver_id',
        'date',
        'total',
        'invoice_path',
        'status'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
