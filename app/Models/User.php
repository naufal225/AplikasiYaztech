<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'division_id',
        'url_profile'
        // ❌ 'role' dihapus — tidak digunakan di sistem multi-role
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
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

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // 🔗 Relasi ke Division
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    // 🔗 Relasi many-to-many ke Role
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // 🔗 Relasi ke Leave (user sebagai employee)
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    // 🔗 Relasi ke Reimbursement
    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class);
    }

    // 🔗 Relasi ke OfficialTravel
    public function officialTravels()
    {
        return $this->hasMany(OfficialTravel::class);
    }

    // 🔗 Relasi ke Overtime
    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES (Opsional tapi direkomendasikan)
    |--------------------------------------------------------------------------
    */

    public function leavesPending()
    {
        return $this->leaves()->where('status', 'pending');
    }

    public function reimbursementsPending()
    {
        return $this->reimbursements()->where('status', 'pending');
    }

    public function officialTravelsPending()
    {
        return $this->officialTravels()->where('status', 'pending');
    }

    public function overtimesPending()
    {
        return $this->overtimes()->where('status', 'pending');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah user memiliki role tertentu.
     */
    public function hasRole(string $roleName): bool
    {
        // Pastikan relasi roles sudah di-load, atau gunakan query
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Cek apakah user memiliki role aktif di session.
     */
    public function hasActiveRole(string $roleName): bool
    {
        return Session::get('active_role') === $roleName;
    }

    /**
     * Ambil role aktif dari session.
     */
    public function getActiveRole(): ?string
    {
        return Session::get('active_role');
    }

    public function getRoleArray() {
        return implode(', ', $this->roles()->toArray());
    }
}
