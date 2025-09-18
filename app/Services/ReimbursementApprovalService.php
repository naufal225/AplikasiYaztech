<?php

namespace App\Services;

use App\Events\ReimbursementLevelAdvanced;
use App\Models\Reimbursement;
use App\Models\User;
use App\Models\ApprovalLink;
use App\Enums\Roles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReimbursementApprovalService
{
    public function handleApproval(Reimbursement $reimbursement, string $status, ?string $note, string $level): void
    {
        // Validasi: tidak boleh update jika sudah final
        if ($level === 'status_1') {
            if ($reimbursement->status_1 !== 'pending') {
                throw new \Exception('Status 1 sudah final dan tidak dapat diubah.');
            }

            if ($status === 'rejected') {
                $reimbursement->update([
                    'status_1' => 'rejected',
                    'note_1' => $note,
                    'status_2' => 'rejected', // cascade
                ]);
                return;
            }

            if ($status === 'approved') {
                $reimbursement->update([
                    'status_1' => 'approved',
                    'note_1' => $note,
                ]);

                event(new ReimbursementLevelAdvanced($reimbursement->fresh(), Auth::user()->division_id, 'manager'));

                // kirim ke manager
                $manager = User::where('role', Roles::Manager->value)->first();
                if ($manager) {
                    $token = Str::random(48);
                    ApprovalLink::create([
                        'model_type' => get_class($reimbursement),
                        'model_id' => $reimbursement->id,
                        'approver_user_id' => $manager->id,
                        'level' => 2,
                        'scope' => 'both',
                        'token' => hash('sha256', $token),
                        'expires_at' => now()->addDays(3),
                    ]);

                    $link = route('public.approval.show', $token);
                    Mail::to($manager->email)->queue(
                        new \App\Mail\SendMessage(
                            namaPengaju: $reimbursement->employee->name,
                            namaApprover: $manager->name,
                            linkTanggapan: $link,
                            emailPengaju: $reimbursement->employee->email
                        )
                    );
                }
            }
        }

        if ($level === 'status_2') {
            if ($reimbursement->status_1 !== 'approved') {
                throw new \Exception('Status 2 hanya dapat diubah setelah status 1 disetujui.');
            }

            if ($reimbursement->status_2 !== 'pending') {
                throw new \Exception('Status 2 sudah final dan tidak dapat diubah.');
            }

            $reimbursement->update([
                'status_2' => $status,
                'note_2' => $note,
            ]);
        }
    }
}
