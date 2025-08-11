<?php

namespace App\Exports;

use App\Models\Reimbursement;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReimbursementsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Reimbursement::with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
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
            'Date',
            'Total',
            'Status',
            'Approver Name',
            'Applied Date',
            'Updated Date',
        ];
    }

    // Perbaiki nama variabel + gunakan optional() agar aman null
    public function map($reimbursement): array
    {
        $date = Carbon::parse($reimbursement->date);

        return [
            '#'.$reimbursement->id,
            optional($reimbursement->employee)->name ?? 'N/A',
            optional($reimbursement->employee)->email ?? 'N/A',
            $date->format('M d, Y'),
            $reimbursement->total ?? 0,
            ucfirst((string) $reimbursement->status),
            optional($reimbursement->approver)->name ?? 'N/A',
            $reimbursement->created_at?->format('M d, Y H:i') ?? '-',
            $reimbursement->updated_at?->format('M d, Y H:i') ?? '-',
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
