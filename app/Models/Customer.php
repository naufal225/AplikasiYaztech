<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name'
    ];

    public function reimbursements() {
        return $this->hasMany(Reimbursement::class, 'customer_id');
    }
}
