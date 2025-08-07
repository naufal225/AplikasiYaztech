<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialTravel extends Model
{
<<<<<<< HEAD
    protected $table = "official_travels";
=======
    protected $table = 'official_travels';

>>>>>>> 3c83858 (Perubahan ke2)
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
}
