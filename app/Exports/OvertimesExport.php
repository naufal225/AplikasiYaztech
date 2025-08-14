<?php

namespace App\Exports;

use App\Models\Overtime;
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
        $query = Overtime::with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

         if (!empty($this->filters['status'])) {
            $query->where('status_1', $this->filters['status'])
                ->orWhere('status_2', $this->filters['status']);
        }


        // Pakai whereDate untuk kolom DATE; lebih aman
        if (!empty($this->filters['from_date'])) {
            $query->whereDate('date_start', '>=', Carbon::parse($this->filters['from_date'])->toDateString());
        }
        if (!empty($this->filters['to_date'])) {
            $query->whereDate('date_start', '<=', Carbon::parse($this->filters['to_date'])->toDateString());
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Request ID',
            'Employee Name',
            'Employee Email',
            'Start Date',
            'End Date',
            'Duration (Days)',
            'Total',
            'Status 1',
            'Status 2',
            'Approver Name',
            'Applied Date',
            'Updated Date',
        ];
    }

    // Perbaiki nama variabel + gunakan optional() agar aman null
    public function map($overtime): array
    {
        $startDate = Carbon::parse($overtime->date_start);
        $endDate   = Carbon::parse($overtime->date_end);
        $duration  = $startDate->diffInDays($endDate) + 1;
        $totalMinutes = $overtime->total;
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return [
            '#'.$overtime->id,
            optional($overtime->employee)->name ?? 'N/A',
            optional($overtime->employee)->email ?? 'N/A',
            $startDate->format('M d, Y'),
            $endDate->format('M d, Y'),
            $duration,
            $hours . " jam, " . $minutes . " menit",
            ucfirst((string) $overtime->status_1),
            ucfirst((string) $overtime->status_2),
            optional($overtime->approver)->name ?? 'N/A',
            $overtime->created_at?->format('M d, Y H:i') ?? '-',
            $overtime->updated_at?->format('M d, Y H:i') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
    }
}
