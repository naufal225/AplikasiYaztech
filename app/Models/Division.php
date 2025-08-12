<?php

namespace App\Models;

use App\Roles;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'leader_id',
        'name'
    ];

    public function members() {
        return $this->hasMany(User::class, 'division_id')
            ->where('role', Roles::Employee->value);
    }

    public function leader() {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function reimbusements() {
        return $this->hasManyThrough(
            Reimbursement::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }

    public function leaves() {
        return $this->hasManyThrough(
            Leave::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }

    public function officialTravels() {
        return $this->hasManyThrough(
            OfficialTravel::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }

    public function overtimes() {
        return $this->hasManyThrough(
            Overtime::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }
}
