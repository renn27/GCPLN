<?php

namespace App\Exports;

use App\Models\Petugas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Petugas::with(['rbms.hasilGc'])->get()->map(function($petugas) {
            $open = $petugas->rbms->sum(fn($rbm) => $rbm->hasilGc->open ?? 0);
            $submitted = $petugas->rbms->sum(fn($rbm) => $rbm->hasilGc->submitted ?? 0);
            $rejected = $petugas->rbms->sum(fn($rbm) => $rbm->hasilGc->rejected ?? 0);
            $total = $open + $submitted + $rejected;
            $persentase = $total > 0 ? round(($submitted / $total) * 100, 2) : 0;
            
            $petugas->total_open = $open;
            $petugas->total_submitted = $submitted;
            $petugas->total_rejected = $rejected;
            $petugas->persentase = $persentase;
            
            return $petugas;
        });
    }

    public function map($petugas): array
    {
        return [
            $petugas->nama,
            $petugas->total_open,
            $petugas->total_submitted,
            $petugas->total_rejected,
            $petugas->persentase . '%'
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Petugas',
            'Open',
            'Submitted',
            'Rejected',
            'Persentase'
        ];
    }
}
