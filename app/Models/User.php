<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    public function leavesNeedApproval() {
        return $this->hasMany(Leave::class, 'approver_id');
    }

    public function leavesThatApplied() {
        return $this->hasMany(Leave::class, 'employee_id');
    }

    //Reimbursements
    public function reimbursementsNeedApproval() {
        return $this->hasMany(Reimbursement::class, 'approver_id');
    }

    public function reimbursementsApplied() {
        return $this->hasMany(Reimbursement::class, 'employee_id');
    }

    //Overtimes
    public function overtimesNeedApproval() {
        return $this->hasMany(Overtime::class, 'approver_id');
    }

    public function overtimesApplied() {
        return $this->hasMany(Overtime::class, 'employee_id');
    }

    //Official Travel
    public function officialTravelNeedApproval() {
        return $this->hasMany(OfficialTravel::class, 'approver_id');
    }

    public function officialTracelApplied() {
        return $this->hasMany(OfficialTravel::class, 'employee_id');
    }
}
