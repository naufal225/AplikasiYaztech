<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;

trait HasDualStatus
{
    /**
     * Override di model kalau nama kolom bukan 'status_1' & 'status_2'.
     * Contoh di model: protected array $finalStatusColumns = ['approval_a', 'approval_b'];
     */
    protected array $finalStatusColumns = ['status_1', 'status_2'];

    protected function getFinalStatusColumns(): array
    {
        // Validasi minimal
        [$c1, $c2] = $this->finalStatusColumns + [null, null];
        if (!$c1 || !$c2) {
            throw new \RuntimeException(static::class . ' must define two status columns');
        }
        return [$c1, $c2];
    }

    /**
     * Scope filter final status: approved/rejected/pending (gabungan 2 kolom).
     */
    public function scopeFilterFinalStatus(Builder $query, ?string $status): Builder
    {
        if (!$status)
            return $query;

        [$s1, $s2] = $this->getFinalStatusColumns();

        return match ($status) {
            'approved' => $query->where($s1, 'approved')->where($s2, 'approved'),

            'rejected' => $query->where(function ($q) use ($s1, $s2) {
                    $q->where($s1, 'rejected')->orWhere($s2, 'rejected');
                }),

            'pending' => $query->where(function ($q) use ($s1, $s2) {
                    // pending = (ada pending) && (tidak ada rejected)
                    $q->where(function ($qq) use ($s1, $s2) {
                        $qq->where($s1, 'pending')->orWhere($s2, 'pending');
                    })->where(function ($qq) use ($s1, $s2) {
                        $qq->where($s1, '!=', 'rejected')->where($s2, '!=', 'rejected');
                    });
                }),

            default => $query,
        };
    }

    /**
     * Scope agregasi count approved/rejected/pending sekali query.
     * NB: pakai CASE/SUM agar mutual-exclusive.
     */
    public function scopeWithFinalStatusCount(Builder $query): Builder
    {
        [$s1, $s2] = $this->getFinalStatusColumns();
        $table = $this->getTable();

        // Jika ingin NULL dianggap 'pending', ganti $s1/$s2 dengan "COALESCE($table.$s1,'pending')" di ekspresi.
        $sql = "
            COUNT(*) as total,
            SUM(
                CASE 
                    WHEN {$table}.{$s1} = 'approved' AND {$table}.{$s2} = 'approved' THEN 1
                    ELSE 0
                END
            ) AS approved,
            SUM(
                CASE 
                    WHEN {$table}.{$s1} = 'rejected' OR {$table}.{$s2} = 'rejected' THEN 1
                    ELSE 0
                END
            ) AS rejected,
            SUM(
                CASE 
                    WHEN {$table}.{$s1} = 'rejected' OR {$table}.{$s2} = 'rejected' THEN 0
                    WHEN {$table}.{$s1} = 'approved' AND {$table}.{$s2} = 'approved' THEN 0
                    ELSE 1
                END
            ) AS pending
        ";

        return $query->selectRaw($sql);
    }

    /** (Opsional) Scope umum yang sering dipakai */
    public function scopeForLeader(Builder $query, int $leaderId): Builder
    {
        return $query->whereHas('employee.division', fn($q) => $q->where('leader_id', $leaderId));
    }

    public function scopeDateRange(Builder $query, ?string $fromDate, ?string $toDate, string $column = 'date_start'): Builder
    {
        if ($fromDate) {
            $query->where($column, '>=', \Carbon\Carbon::parse($fromDate)->startOfDay()->timezone('Asia/Jakarta'));
        }
        if ($toDate) {
            $query->where($column, '<=', \Carbon\Carbon::parse($toDate)->endOfDay()->timezone('Asia/Jakarta'));
        }
        return $query;
    }

    public function getFinalStatusAttribute(): string
    {
        // Normalisasi, kalau mau NULL dianggap 'pending'
        $s1 = $this->status_1 ?? 'pending';
        $s2 = $this->status_2 ?? 'pending';

        if ($s1 === 'rejected' || $s2 === 'rejected') {
            return 'rejected';
        }

        if ($s1 === 'approved' && $s2 === 'approved') {
            return 'approved';
        }

        // default pending
        return 'pending';
    }

}
