<?php

namespace App\Imports;

use App\Models\HasilGc;
use App\Models\Rbm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class GcImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel terlihat kosong.');
        }

        $firstRow = $rows->first();
        if (!isset($firstRow['rbm']) || !isset($firstRow['open']) || !isset($firstRow['submitted']) || !isset($firstRow['rejected'])) {
            throw new \Exception('Kolom wajib (RBM, OPEN, SUBMITTED, REJECTED) tidak ditemukan. Pastikan format kolom sesuai.');
        }

        DB::transaction(function () use ($rows) {
            HasilGc::query()->delete();

            foreach ($rows as $row) {
                if (!isset($row['rbm'])) {
                    continue;
                }

                $rbm = Rbm::where('kode_rbm', $row['rbm'])->first();
                if (!$rbm) {
                    continue;
                }

                HasilGc::create([
                    'rbm_id' => $rbm->id,
                    'open' => (int) ($row['open'] ?? 0),
                    'submitted' => (int) ($row['submitted'] ?? 0),
                    'rejected' => (int) ($row['rejected'] ?? 0),
                ]);
            }
        });
    }
}
