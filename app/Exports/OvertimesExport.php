<?php

namespace App\Exports;

use App\Models\Overtime;
use App\Models\User;
use App\Roles;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OvertimesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        // Bangun query dasar dengan eager loading
        $query = Overtime::with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        // Terapkan filter status
        if (!empty($this->filters['status'])) {
            $statusFilter = $this->filters['status'];
            switch ($statusFilter) {
                case 'approved':
                    $query->where('status_1', 'approved')
                        ->where('status_2', 'approved');
                    break;
                case 'rejected':
                    $query->where(function ($q) {
                        $q->where('status_1', 'rejected')
                            ->orWhere('status_2', 'rejected');
                    });
                    break;
                case 'pending':
                    // Logika "pending" yang kompleks
                    $query->where(function ($q) {
                        // Kondisi 1: Minimal satu status adalah 'pending'
                        $q->where(function ($qq) {
                            $qq->where('status_1', 'pending')
                                ->orWhere('status_2', 'pending');
                        });
                        // Kondisi 2: Tidak ada status yang 'rejected'
                        $q->where(function ($qq) {
                            $qq->where('status_1', '!=', 'rejected')
                                ->where('status_2', '!=', 'rejected');
                        });
                    });
                    break;
                // Tidak ada case default, jadi jika status tidak valid, tidak ada filter
            }
        }

        // Terapkan filter tanggal berdasarkan created_at
        if (!empty($this->filters['from_date'])) {
            $fromDate = Carbon::parse($this->filters['from_date'])->startOfDay()->timezone('Asia/Jakarta');
            $query->where('created_at', '>=', $fromDate);
        }
        if (!empty($this->filters['to_date'])) {
            $toDate = Carbon::parse($this->filters['to_date'])->endOfDay()->timezone('Asia/Jakarta');
            $query->where('created_at', '<=', $toDate);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Request ID',
            'Employee Name',
            'Employee Email',
            'Start Date & Time',
            'End Date & Time',
            'Duration (Days)',
            'Overtime (Hours & Minutes)', // Nama kolom diperjelas
            'Meal Costs (Rp)',           // Kolom baru
            'Overtime Rate (Rp)',        // Kolom baru
            'Total Amount (Rp)',         // Kolom baru
            'Status 1',
            'Status 2',
            'Approver 1',
            'Approver 2',
            'Applied Date',
            'Updated Date',
        ];
    }

    // Perbaiki nama variabel + gunakan optional() agar aman null
    public function map($overtime): array
    {
        // Parsing waktu input
        // Pastikan zona waktu konsisten, misal 'Asia/Jakarta'
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $overtime->date_start, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $overtime->date_end, 'Asia/Jakarta');

        // 1. Hitung durasi hari (inklusif)
        $durationDays = round($start->diffInDays($end) + 1, 2);

        // 2. Hitung durasi lembur dalam menit
        $overtimeMinutes = $start->diffInMinutes($end);

        // 3. Konversi menit ke jam dan menit
        $hours = floor($overtimeMinutes / 60);
        $minutes = $overtimeMinutes % 60;

        // 4. Ambil nilai konfigurasi dari .env atau default
        $mealCost = (int) env('MEAL_COSTS', 30000);
        $overtimeRatePerHour = (int) env('OVERTIME_COSTS', 25000);

        // 5. Hitung total amount berdasarkan logika:
        //    (Jam Lembur * Tarif/Jam) + Uang Makan
        //    Catatan: Ini mengasumsikan $overtime->total menyimpan menit.
        //    Jika $overtime->total sudah menyimpan nilai total akhir, gunakan itu.
        //    Berdasarkan schema dan logika sebelumnya, $overtime->total adalah hasil perhitungan.
        //    Jadi kita bisa gunakan langsung atau hitung ulang.
        //    Kita hitung ulang untuk konsistensi dan transparansi di export.
        $calculatedTotal = ($hours * $overtimeRatePerHour) + $mealCost;

        return [
            '#' . $overtime->id,
            optional($overtime->employee)->name ?? 'N/A',
            optional($overtime->employee)->email ?? 'N/A',
            // Format tanggal & waktu
            $start->format('M d, Y H:i'),
            $end->format('M d, Y H:i'),
            // Durasi hari bulat
            $durationDays,
            // Durasi lembur dalam format jam & menit
            $hours . " jam, " . $minutes . " menit",
            // Meal Costs
            number_format($mealCost, 0, ',', '.'),
            // Overtime Rate (dihitung berdasarkan jam penuh)
            number_format($hours * $overtimeRatePerHour, 0, ',', '.'),
            // Total Amount
            number_format($calculatedTotal, 0, ',', '.'),
            // Status
            ucfirst((string) $overtime->status_1),
            ucfirst((string) $overtime->status_2),
            // Approver
            optional($overtime->approver)->name ?? 'N/A',
            optional(User::where('role', Roles::Manager->value)->first())->name ?? 'N/A',
            // Tanggal pengajuan & update
            $overtime->created_at?->timezone('Asia/Jakarta')->format('M d, Y H:i') ?? '-',
            $overtime->updated_at?->timezone('Asia/Jakarta')->format('M d, Y H:i') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk baris header (baris pertama)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'], // Warna latar belakang header
                ],
            ],
        ];
    }
}
