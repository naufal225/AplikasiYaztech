<?php

namespace App\Services;

use App\Models\OfficialTravel;
use App\Models\User;
use App\Models\ApprovalLink;
use App\Roles;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OfficialTravelService
{
    public function create(array $data): OfficialTravel
    {
        return DB::transaction(function () use ($data) {
            $start = Carbon::parse($data['date_start']);
            $end = Carbon::parse($data['date_end']);
            $days = $start->diffInDays($end) + 1;

            $travel = OfficialTravel::create([
                'employee_id' => Auth::id(),
                'customer' => $data['customer'],
                'date_start' => $start,
                'date_end' => $end,
                'total' => $days * (int) env('TRAVEL_COSTS_PER_DAY', 0),
                'status_1' => 'pending',
                'status_2' => 'pending',
            ]);

            return $travel;
        });
    }

    public function update(OfficialTravel $travel, array $data)
    {
        if ($travel->status_1 !== 'pending' || $travel->status_2 !== 'pending') {
            throw new Exception('Travel request sudah diproses, tidak bisa diupdate.');
        }

        $start = Carbon::parse($data['date_start']);
        $end = Carbon::parse($data['date_end']);
        $days = $start->diffInDays($end) + 1;

        $travel->update([
            'customer' => $data['customer'],
            'date_start' => $start,
            'date_end' => $end,
            'total' => $days * (int) env('TRAVEL_COSTS_PER_DAY', 0),
            'status_1' => 'pending',
            'status_2' => 'pending',
            'note_1' => null,
            'note_2' => null,
        ]);

        return $travel;
    }

    public function approve(OfficialTravel $travel, string $role, string $status, ?string $note): OfficialTravel
    {
        if ($role === Roles::Approver->value) {
            $travel->update([
                'status_1' => $status,
                'note_1' => $note,
                'status_2' => $status === 'rejected' ? 'rejected' : $travel->status_2,
            ]);
        }

        if ($role === Roles::Manager->value) {
            if ($travel->status_1 !== 'approved') {
                throw new \Exception('Approver 2 hanya bisa memproses jika Approver 1 sudah approve.');
            }
            $travel->update([
                'status_2' => $status,
                'note_2' => $note,
            ]);
        }

        return $travel;
    }
}
