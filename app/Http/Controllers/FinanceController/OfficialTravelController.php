<?php

namespace App\Http\Controllers\FinanceController;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfficialTravelRequest;
use App\Http\Requests\UpdateOfficialTravelRequest;
use App\Models\OfficialTravel;
use App\Models\User;
use App\Services\OfficialTravelService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OfficialTravelController extends Controller
{
    public function __construct(private OfficialTravelService $officialTravelService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // --- Query untuk "Your Official Travels"
        $yourTravelsQuery = OfficialTravel::with(['employee', 'approver'])
            ->where('employee_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $yourTravelsQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $yourTravelsQuery->where('date_end', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $yourTravelsQuery->where(function ($q) use ($status) {
                if ($status === 'rejected') {
                    $q->where('status_1', 'rejected')
                    ->orWhere('status_2', 'rejected');
                } elseif ($status === 'approved') {
                    $q->where('status_1', 'approved')
                    ->where('status_2', 'approved');
                } elseif ($status === 'pending') {
                    $q->where(function ($sub) {
                        $sub->where('status_1', 'pending')
                            ->orWhere('status_2', 'pending');
                    })
                    ->where('status_1', '!=', 'rejected')
                    ->where('status_2', '!=', 'rejected')
                    ->where(function ($sub) {
                        $sub->where('status_1', '!=', 'approved')
                            ->orWhere('status_2', '!=', 'approved');
                    });
                }
            });
        }

        $yourTravels = $yourTravelsQuery->paginate(5, ['*'], 'your_page')->withQueryString();

        // --- Query untuk "All Official Travels Done (Marked Down)"
        $allTravelsDoneQuery = OfficialTravel::with(['employee', 'approver'])
            ->where('status_1', 'approved')
            ->where('status_2', 'approved')
            ->where('marked_down', true)
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $allTravelsDoneQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $allTravelsDoneQuery->where('date_end', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $allTravelsDone = $allTravelsDoneQuery->paginate(5, ['*'], 'all_page_done')->withQueryString();

        // --- Query untuk "All Official Travels Not Marked (lockable)"
        $allTravels = collect();
        DB::transaction(function () use (&$allTravels, $request, $userId) {
            $query = OfficialTravel::with(['employee', 'approver'])
                ->where('status_1', 'approved')
                ->where('status_2', 'approved')
                ->where('marked_down', false)
                ->where(function ($q) use ($userId) {
                    $q->whereNull('locked_by')
                    ->orWhere(function ($q2) use ($userId) {
                        $q2->whereRaw('DATE_ADD(locked_at, INTERVAL 60 MINUTE) < ?', [now()]);
                    })
                    ->orWhere(function ($q3) use ($userId) {
                        $q3->where('locked_by', $userId)
                            ->whereRaw('DATE_ADD(locked_at, INTERVAL 60 MINUTE) >= ?', [now()]);
                    });
                })
                ->orderBy('created_at', 'asc');

            if (request()->filled('from_date')) {
                $query->where('date_start', '>=',
                    Carbon::parse(request()->from_date)->startOfDay()->timezone('Asia/Jakarta')
                );
            }

            if (request()->filled('to_date')) {
                $query->where('date_end', '<=',
                    Carbon::parse(request()->to_date)->endOfDay()->timezone('Asia/Jakarta')
                );
            }

            $allTravels = $query->limit(5)->lockForUpdate()->get();

            if ($allTravels->isNotEmpty()) {
                OfficialTravel::whereIn('id', $allTravels->pluck('id'))
                    ->update([
                        'locked_by' => $userId,
                        'locked_at' => now(),
                    ]);
            }
        });

        // --- Statistik
        $dataAll = OfficialTravel::where('status_1', 'approved')
            ->where('status_2', 'approved');

        $totalRequests = $dataAll->count();
        $approvedRequests = optional($dataAll->withFinalStatusCount()->first())->approved ?? 0;
        $markedRequests = (clone $dataAll)->where('marked_down', true)->count();
        $totalAllNoMark = (clone $dataAll)->where('marked_down', false)->count();

        $countsYours = (clone $yourTravelsQuery)->withFinalStatusCount()->first();
        $totalYoursRequests = $yourTravelsQuery->count();
        $pendingYoursRequests = optional($countsYours)->pending ?? 0;
        $approvedYoursRequests = optional($countsYours)->approved ?? 0;
        $rejectedYoursRequests = optional($countsYours)->rejected ?? 0;

        // --- Manager
        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.travels.travel-show', compact(
            'yourTravels',
            'allTravels',
            'allTravelsDone',
            'totalRequests',
            'approvedRequests',
            'markedRequests',
            'totalAllNoMark',
            'totalYoursRequests',
            'pendingYoursRequests',
            'approvedYoursRequests',
            'rejectedYoursRequests',
            'manager'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('Finance.travels.travel-request', compact('approvers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfficialTravelRequest $request)
    {
        try {
            $this->officialTravelService->store($request->validated());

            return redirect()->route('finance.official-travels.index')
                ->with('success', 'Official travel request submitted successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(OfficialTravel $officialTravel)
    {
        $officialTravel->load(['employee', 'approver']);
        return view('Finance.travels.travel-detail', compact('officialTravel'));
    }

    /**
     * Mark selected overtimes as done (marked_down = true).
     */

    public function markedDone(Request $request)
    {
        $ids = $request->input('ids', []);

        try {
            DB::transaction(function () use ($ids) {
                $records = OfficialTravel::whereIn('id', $ids)
                    ->where('marked_down', false)
                    ->where('locked_by', Auth::id())
                    ->lockForUpdate()
                    ->get();

                if ($records->isEmpty()) {
                    throw new Exception('No official travels available to mark as done.');
                }

                foreach ($records as $rec) {
                    $rec->update([
                        'marked_down' => true,
                        'locked_by'   => null,
                        'locked_at'   => null,
                    ]);
                }
            });

            return redirect()
                ->route('finance.official-travels.index')
                ->with('success', 'Selected official travels marked as done.');
        } catch (Exception $e) {
            return redirect()
                ->route('finance.official-travels.index')
                ->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        $isLeader = \App\Models\Division::where('leader_id', $user->id)->exists();

        if (($isLeader && $officialTravel->status_2 !== 'pending') || (!$isLeader && $officialTravel->status_1 !== 'pending' || $officialTravel->status_2 !== 'pending')) {
            return redirect()->route('finance.official-travels.show', $officialTravel->id)
                ->with('error', 'You cannot edit a travel request that has already been processed.');
        }

        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('Finance.travels.travel-edit', compact('officialTravel', 'approvers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficialTravelRequest $request, OfficialTravel $officialTravel)
    {
        $user = Auth::user();

        if ($user->id !== $officialTravel->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->officialTravelService->update($officialTravel, $request->validated());

            return redirect()->route('finance.official-travels.index')
                ->with('success', 'Official travel request updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        $isLeader = \App\Models\Division::where('leader_id', $user->id)->exists();

        if (($isLeader && $officialTravel->status_2 !== 'pending') || (!$isLeader && $officialTravel->status_1 !== 'pending')) {
            return redirect()->route('finance.official-travels.show', $officialTravel->id)
                ->with('error', 'You cannot delete a travel request that has already been processed.');
        }

        if (\App\Models\ApprovalLink::where('model_id', $officialTravel->id)->where('model_type', get_class($officialTravel))->exists()) {
            \App\Models\ApprovalLink::where('model_id', $officialTravel->id)->where('model_type', get_class($officialTravel))->delete();
        }

        $officialTravel->delete();

        return redirect()->route('finance.official-travels.index')
            ->with('success', 'Official travel request deleted successfully.');
    }

    /**
     * Bulk export approved requests as PDFs in a ZIP file.
     */
    public function bulkExport(Request $request)
    {
        $dateFrom = $request->input('from_date');
        $dateTo = $request->input('date_to');

        $query = OfficialTravel::with('employee')->where('status_1', 'approved')->where('status_2', 'approved')->where('marked_down', true);

        if ($dateFrom && $dateTo) {
            $query->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('date_start', '<=', $dateTo)
                ->whereDate('date_end', '>=', $dateFrom);
            });
        }

        $officialTravels = $query->get();

        if ($officialTravels->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk filter tersebut.');
        }

        $zipFileName = 'OfficialTravelsRequests_' . Carbon::now()->format('YmdHis') . '.zip';
        $zipPath = Storage::disk('public')->path($zipFileName);

        // Folder sementara untuk menyimpan PDF
        $tempFolder = 'temp_official_travels';
        if (!Storage::disk('public')->exists($tempFolder)) {
            Storage::disk('public')->makeDirectory($tempFolder);
        }

        $files = [];

        foreach ($officialTravels as $officialTravel) {
            $pdf = Pdf::loadView('Finance.travels.pdf', compact('officialTravel'));
            $fileName = "official_travel_{$officialTravel->employee->name}_" . $officialTravel->id . ".pdf";
            $filePath = "{$tempFolder}/{$fileName}";
            Storage::disk('public')->put($filePath, $pdf->output());
            $files[] = Storage::disk('public')->path($filePath);
        }

        // Buat ZIP
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        // Bersihkan file sementara
        foreach ($files as $file) {
            @unlink($file);
        }
        Storage::disk('public')->deleteDirectory($tempFolder);

        // Return download
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(OfficialTravel $officialTravel)
    {
        $pdf = Pdf::loadView('Finance.travels.pdf', compact('officialTravel'));
        return $pdf->download('official-travel-details-finance.pdf');
    }
}
