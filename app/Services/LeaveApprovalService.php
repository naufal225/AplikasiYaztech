<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\User;
use App\Enums\Roles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LeaveApprovalService
{
    /**
     * Approve a leave request.
     */
    public function approve(Leave $leave, ?string $note = null): Leave
    {
        $this->authorizeManager();

        if ($leave->status_1 !== 'pending') {
            throw ValidationException::withMessages([
                'status_1' => 'Leave sudah diproses, tidak dapat diubah lagi.'
            ]);
        }

        $leave->update([
            'status_1' => 'approved',
            'note_1'   => $note ?? null,
        ]);

        return $leave;
    }

    /**
     * Reject a leave request.
     */
    public function reject(Leave $leave, ?string $note = null): Leave
    {
        $this->authorizeManager();

        if ($leave->status_1 !== 'pending') {
            throw ValidationException::withMessages([
                'status_1' => 'Leave sudah diproses, tidak dapat diubah lagi.'
            ]);
        }

        $leave->update([
            'status_1' => 'rejected',
            'note_1'   => $note ?? null,
        ]);

        return $leave;
    }

    private function authorizeManager(): void
    {
        if (Auth::user()->role !== Roles::Manager->value) {
            abort(403, 'Unauthorized – hanya Manager yang bisa approve/reject leave.');
        }
    }
}
