<?php

namespace App\Imports;

use App\Models\HasilGc;
use App\Models\Petugas;
use App\Models\Rbm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class GcImport implements ToCollection, WithHeadingRow
{
    public function __construct(private string $jenisLayanan = 'pascabayar')
    {
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel terlihat kosong.');
        }

        $firstRow = $rows->first()->toArray();
        $requiredColumns = $this->jenisLayanan === 'prabayar'
            ? ['email_biller', 'submitted', 'rejected']
            : ['rbm', 'open', 'submitted', 'rejected'];

        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $firstRow)) {
                $label = $this->jenisLayanan === 'prabayar'
                    ? 'EMAIL BILLER, SUBMITTED, REJECTED'
                    : 'RBM, OPEN, SUBMITTED, REJECTED';

                throw new \Exception("Kolom wajib ({$label}) tidak ditemukan. Pastikan format kolom sesuai.");
            }
        }

        DB::transaction(function () use ($rows) {
            HasilGc::where('jenis_layanan', $this->jenisLayanan)->delete();

            foreach ($rows as $row) {
                $rbm = $this->jenisLayanan === 'prabayar'
                    ? $this->findRbmByEmailBiller($row['email_biller'] ?? null)
                    : $this->findRbmByCode($row['rbm'] ?? null);

                if (!$rbm) {
                    continue;
                }

                HasilGc::create([
                    'rbm_id' => $rbm->id,
                    'jenis_layanan' => $this->jenisLayanan,
                    'open' => $this->jenisLayanan === 'prabayar' ? 0 : (int) ($row['open'] ?? 0),
                    'submitted' => (int) ($row['submitted'] ?? 0),
                    'rejected' => (int) ($row['rejected'] ?? 0),
                ]);
            }
        });
    }

    private function findRbmByCode(?string $kodeRbm): ?Rbm
    {
        if (!$kodeRbm) {
            return null;
        }

        return Rbm::where('kode_rbm', trim($kodeRbm))->first();
    }

    private function findRbmByEmailBiller(?string $emailBiller): ?Rbm
    {
        $emailBiller = strtolower(trim((string) $emailBiller));

        if ($emailBiller === '') {
            return null;
        }

        $petugas = Petugas::with('rbms')
            ->whereRaw('LOWER(email) = ?', [$emailBiller])
            ->first();

        return $petugas?->rbms->first();
    }
}
