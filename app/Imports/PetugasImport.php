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
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (empty($row['rbm']) || empty($row['nama_petugas'])) {
                    continue;
                }

                // BERSIHKAN EMAIL: lowercase dan trim
                $email = strtolower(trim($row['email'] ?? ''));

                // Cari atau buat petugas
                $petugas = Petugas::firstOrCreate(
                    ['email' => $email],
                    ['nama' => trim($row['nama_petugas'])]
                );

                // Buat RBM
                Rbm::firstOrCreate(
                    ['kode_rbm' => trim($row['rbm'])],
                    ['petugas_id' => $petugas->id]
                );
            }
        });
    }
}