<?php

namespace App\Imports;

use App\Models\Petugas;
use App\Models\Rbm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class PetugasImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel terlihat kosong.');
        }

        $firstRow = $rows->first();
        if (!isset($firstRow['rbm']) || !isset($firstRow['email'])) {
            throw new \Exception('Kolom wajib (RBM, Email) tidak ditemukan di file. Pastikan format kolom baris pertama sesuai dengan contoh.');
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (!isset($row['rbm']) || !isset($row['email'])) {
                    continue;
                }

                $petugas = Petugas::firstOrCreate(
                    ['email' => $row['email']],
                    ['nama' => $row['nama_petugas'] ?? ($row['nama'] ?? $row['email'])]
                );

                Rbm::updateOrCreate(
                    ['kode_rbm' => $row['rbm']],
                    ['petugas_id' => $petugas->id]
                );
            }
        });
    }
}
