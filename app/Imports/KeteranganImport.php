<?php

namespace App\Imports;

use App\Models\Keterangan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class KeteranganImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel terlihat kosong.');
        }

        $firstRow = $rows->first();
        
        if (!isset($firstRow['unitupi']) || 
            !isset($firstRow['unitap']) || 
            !isset($firstRow['unitup']) ||
            !isset($firstRow['email_biller']) ||
            !isset($firstRow['1_berhasil_didata']) || 
            !isset($firstRow['2_tidak_ada_responden_yang_dapat_memberi_jawabanrumah_kosong']) || 
            !isset($firstRow['3_responden_menolak']) || 
            !isset($firstRow['4_meteran_tidak_ditemukan'])) {
            throw new \Exception('Kolom wajib tidak ditemukan. Pastikan format kolom sesuai (termasuk Email Biller).');
        }

        DB::transaction(function () use ($rows) {
            Keterangan::query()->delete();

            foreach ($rows as $row) {
                if (!isset($row['unitupi'])) {
                    continue;
                }

                $unitupi = trim(str_replace(['[', ']'], '', $row['unitupi'] ?? ''));
                $unitap = trim(str_replace(['[', ']'], '', $row['unitap'] ?? ''));
                $unitup = trim(str_replace(['[', ']'], '', $row['unitup'] ?? ''));
                
                // BERSIHKAN EMAIL: lowercase, trim, hapus spasi berlebih
                $emailBiller = strtolower(trim($row['email_biller'] ?? ''));

                Keterangan::create([
                    'unitupi'                   => $unitupi,
                    'unitap'                    => $unitap,
                    'unitup'                    => $unitup,
                    'email_biller'              => $emailBiller,
                    'berhasil_didata'           => (int) ($row['1_berhasil_didata'] ?? 0),
                    'tidak_ada_responden'       => (int) ($row['2_tidak_ada_responden_yang_dapat_memberi_jawabanrumah_kosong'] ?? 0),
                    'responden_menolak'         => (int) ($row['3_responden_menolak'] ?? 0),
                    'meteran_tidak_ditemukan'   => (int) ($row['4_meteran_tidak_ditemukan'] ?? 0),
                ]);
            }
        });
    }
}