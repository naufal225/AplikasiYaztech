<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\User;
use App\Models\ApprovalLink;
use App\Roles;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LeaveService
{
    public function hitungHariCuti($dateStart, $dateEnd, $tahun = null): int
    {
        $tahun = $tahun ?? now()->year;

        $start = Carbon::parse($dateStart);
        $end = Carbon::parse($dateEnd);

        if ($start->year < $tahun)
            $start = Carbon::create($tahun, 1, 1);
        if ($end->year > $tahun)
            $end = Carbon::create($tahun, 12, 31);

        return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
    }

    public function sisaCuti(User $user, $excludeLeaveId = null): int
    {
        $tahun = now()->year;

        $total = Leave::where('employee_id', $user->id)
            ->when($excludeLeaveId, fn($q) => $q->where('id', '!=', $excludeLeaveId))
            ->where('status_1', 'approved')
            ->get()
            ->sum(fn($cuti) => $this->hitungHariCuti($cuti->date_start, $cuti->date_end, $tahun));

        return (int) env('CUTI_TAHUNAN', 20) - $total;
    }

    public function createLeave(array $data): Leave
    {
        $leave = new Leave();
        $leave->employee_id = Auth::id();
        $leave->date_start = $data['date_start'];
        $leave->date_end = $data['date_end'];
        $leave->reason = $data['reason'];
        $leave->status_1 = 'pending';
        $leave->save();

        $this->kirimNotifikasi($leave);

        return $leave;
    }

    public function updateLeave(Leave $leave, array $data): Leave
    {
        $leave->date_start = $data['date_start'];
        $leave->date_end = $data['date_end'];
        $leave->reason = $data['reason'];
        $leave->status_1 = 'pending';
        $leave->note_1 = null;
        $leave->save();

        $this->kirimNotifikasi($leave);

        return $leave;
    }

    public function kirimNotifikasi(Leave $leave): void
    {
        $manager = User::where('role', Roles::Manager->value)->first();
        if (!$manager)
            return;

        $token = Str::random(48);
        ApprovalLink::create([
            'model_type' => get_class($leave),
            'model_id' => $leave->id,
            'approver_user_id' => $manager->id,
            'level' => 1,
            'scope' => 'both',
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(3),
        ]);

        $linkTanggapan = route('public.approval.show', $token);

        Mail::to($manager->email)->send(
            new \App\Mail\SendMessage(
                namaPengaju: $leave->employee->name,
                namaApprover: $manager->name,
                linkTanggapan: $linkTanggapan,
                emailPengaju: $leave->employee->email
            )
        );
    }
}
