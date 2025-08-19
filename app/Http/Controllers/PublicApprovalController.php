<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalLink;
use Illuminate\Support\Facades\DB;

class PublicApprovalController extends Controller
{
    public function show(string $token)
    {
        $link = ApprovalLink::where('token', hash('sha256', $token))->first();
        abort_if(!$link || !$link->isValid(), 410, 'Link invalid atau kadaluarsa.');

        $subject = $link->subject; // contoh: Leave/OfficialTravel dsb
        // Batasi aksi sesuai scope
        $canApprove = in_array($link->scope, ['approve', 'both']);
        $canReject = in_array($link->scope, ['reject', 'both']);

        return view('public-approval.show', compact('link', 'subject', 'canApprove', 'canReject'));
    }

    public function act(Request $request, string $token)
    {
        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'note' => 'nullable|string'
        ]);

        $link = ApprovalLink::where('token', hash('sha256', $token))->lockForUpdate()->first();
        abort_if(!$link || !$link->isValid(), 410, 'Link invalid atau kadaluarsa.');

        // Cek scope
        if ($validated['action'] === 'approved' && !in_array($link->scope, ['approve', 'both']))
            abort(403);
        if ($validated['action'] === 'rejected' && !in_array($link->scope, ['reject', 'both']))
            abort(403);

        // Ambil subject polymorphic
        $subject = $link->subject;

        DB::transaction(function () use ($validated, $link, $subject, $request) {
            // Terapkan aturan level (status_1 atau status_2) sesuai desainmu
            if ($link->level === 1) {
                if ($subject->status_1 !== 'pending')
                    abort(422, 'Status 1 sudah final.');
                if ($validated['action'] === 'rejected') {
                    $subject->update([
                        'status_1' => 'rejected',
                        'note_1' => $validated['note'] ?? null,
                        'status_2' => 'rejected',
                        'note_2' => $validated['note'] ?? null,
                    ]);
                } else {
                    $subject->update([
                        'status_1' => 'approved',
                        'note_1' => $validated['note'] ?? null,
                    ]);
                    // (Opsional) kirim email ke manager di sini, atau di listener event
                }
            } elseif ($link->level === 2) {
                if ($subject->status_1 !== 'approved')
                    abort(422, 'Status 2 hanya setelah status 1 approved.');
                if ($subject->status_2 !== 'pending')
                    abort(422, 'Status 2 sudah final.');
                $subject->update([
                    'status_2' => $validated['action'],
                    'note_2' => $validated['note'] ?? null,
                ]);
            }

            // Burn token
            $link->update([
                'used_at' => now(),
                'used_ip' => $request->ip(),
                'used_ua' => substr($request->userAgent() ?? '', 0, 255),
            ]);
        });

        return view('public-approval.done'); // atau redirect ke halaman sukses statis
    }
}
