<?php

namespace App\Exports;

use App\Models\Petugas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private string $jenisLayanan = 'pascabayar')
    {
    }

    public function collection()
    {
        return Petugas::with(['rbms.hasilGc' => function ($query) {
            $query->where('jenis_layanan', $this->jenisLayanan);
        }])->get()->map(function($petugas) {
            $open = $this->jenisLayanan === 'prabayar'
                ? 0
                : $petugas->rbms->sum(fn($rbm) => $rbm->hasilGc->open ?? 0);
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
        $row = [
            $this->jenisLayanan === 'prabayar' ? 'Prabayar' : 'Pascabayar',
            $petugas->nama,
            $petugas->total_submitted,
            $petugas->total_rejected,
            $petugas->persentase . '%'
        ];

        if ($this->jenisLayanan !== 'prabayar') {
            array_splice($row, 2, 0, [$petugas->total_open]);
        }

        return $row;
    }

    public function headings(): array
    {
        $headings = [
            'Layanan',
            'Nama Petugas',
            'Submitted',
            'Rejected',
            'Persentase'
        ];

        if ($this->jenisLayanan !== 'prabayar') {
            array_splice($headings, 2, 0, ['Open']);
        }

        return $headings;
    }
}
