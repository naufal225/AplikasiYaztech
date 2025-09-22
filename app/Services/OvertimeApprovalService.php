<?php

namespace App\Services;

use App\Models\Overtime;
use App\Models\User;
use App\Models\ApprovalLink;
use App\Enums\Roles;
use App\Events\OvertimeLevelAdvanced;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OvertimeApprovalService
{
    public function handleApproval(Overtime $overtime, string $status, ?string $note, string $level): void
    {
        // === LEVEL 1 (APPROVER) ===
        if ($level === 'status_1') {
            if ($overtime->status_1 !== 'pending') {
                throw new \Exception('Status 1 sudah final dan tidak dapat diubah.');
            }

            if ($status === 'rejected') {
                $overtime->update([
                    'status_1' => 'rejected',
                    'note_1' => $note,
                    'rejected_date' => Carbon::now(),
                    'status_2' => 'rejected', // cascade reject
                ]);
                return;
            }

            if ($status === 'approved') {
                $overtime->update([
                    'status_1' => 'approved',
                    'note_1' => $note,
                ]);

                event(new OvertimeLevelAdvanced($overtime->fresh(), Auth::user()->division_id, 'manager'));

                // Kirim approval ke Manager
                $manager = User::where('role', Roles::Manager->value)->first();
                if ($manager) {
                    $token = Str::random(48);
                    ApprovalLink::create([
                        'model_type' => get_class($overtime),
                        'model_id' => $overtime->id,
                        'approver_user_id' => $manager->id,
                        'level' => 2,
                        'scope' => 'both',
                        'token' => hash('sha256', $token),
                        'expires_at' => now()->addDays(3),
                    ]);

                    $link = route('public.approval.show', $token);
                    Mail::to($manager->email)->queue(
                        new \App\Mail\SendMessage(
                            namaPengaju: $overtime->employee->name,
                            namaApprover: $manager->name,
                            linkTanggapan: $link,
                            emailPengaju: $overtime->employee->email
                        )
                    );
                }
            }
        }

        // === LEVEL 2 (MANAGER) ===
        if ($level === 'status_2') {
            if ($overtime->status_1 !== 'approved') {
                throw new \Exception('Status 2 hanya dapat diubah setelah status 1 disetujui.');
            }

            if ($overtime->status_2 !== 'pending') {
                throw new \Exception('Status 2 sudah final dan tidak dapat diubah.');
            }

            $overtime->update([
                'status_2' => $status,
                'note_2' => $note,
            ]);

            if ($status == 'approved') {
                $overtime->update([
                    'approved_date' => Carbon::now()
                ]);
            } else {
                $overtime->update([
                    'rejected_date' => Carbon::now()
                ]);
            }
        }
    }
}
