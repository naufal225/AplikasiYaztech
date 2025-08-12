<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    //Leaves
    public function leaves() {
        return $this->hasManyThrough(
            Leave::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }

    public function leavesPending() {
        $this->leaves()->where('status', 'pending');
    }

    //Reimbursements
    public function reimbursements() {
        return $this->hasManyThrough(
            Reimbursement::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }

    public function reimbursementsPending() {
        $this->reimbursements()->where('status', 'pending');
    }

    //OfficialTravels
     public function officialTravels() {
        return $this->hasManyThrough(
            OfficialTravel::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }

    public function officialTravelsPending() {
        $this->officialTravels()->where('status', 'pending');
    }

    //Overtimes
     public function overtimes() {
        return $this->hasManyThrough(
            Overtime::class,
            User::class,
            'id',
            'id'
        )->where('users.role', Roles::Employee->value);
    }

    public function overtimesPending() {
        $this->overtimes()->where('status', 'pending');
    }

    //Division
    public function division() {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
