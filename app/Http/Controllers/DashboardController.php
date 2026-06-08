<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petugas;
use App\Models\HasilGc;
use App\Models\Keterangan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $layananOptions = [
            'pascabayar' => 'Pascabayar',
            'prabayar' => 'Prabayar',
        ];
        $activeLayanan = array_key_exists($request->query('layanan'), $layananOptions)
            ? $request->query('layanan')
            : 'pascabayar';
        $activeLayananLabel = $layananOptions[$activeLayanan];
        $activeTheme = $activeLayanan === 'prabayar'
            ? [
                'accent' => 'emerald',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'gradient' => 'from-emerald-600 to-teal-600',
            ]
            : [
                'accent' => 'blue',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                'gradient' => 'from-blue-600 to-sky-600',
            ];

        // Query petugas dengan relasi hasilGc
        $petugases = Petugas::with(['rbms.hasilGc' => function ($query) use ($activeLayanan) {
                $query->where('jenis_layanan', $activeLayanan);
            }])
            ->get()
            ->map(function($petugas) use ($activeLayanan) {
                // Hitung GC
                $open = $activeLayanan === 'prabayar'
                    ? 0
                    : $petugas->rbms->sum(fn($rbm) => $rbm->hasilGc->open ?? 0);
                $submitted = $petugas->rbms->sum(fn($rbm) => $rbm->hasilGc->submitted ?? 0);
                $rejected = $petugas->rbms->sum(fn($rbm) => $rbm->hasilGc->rejected ?? 0);
                $total = $open + $submitted + $rejected;
                $persentase = $total > 0 ? round(($submitted / $total) * 100, 2) : 0;
                
                // Set data GC
                $petugas->total_open = $open;
                $petugas->total_submitted = $submitted;
                $petugas->total_rejected = $rejected;
                $petugas->persentase = $persentase;
                
                // CARI DATA KETERANGAN SECARA MANUAL (CASE-INSENSITIVE)
                $keterangan = Keterangan::where('jenis_layanan', $activeLayanan)
                    ->whereRaw('LOWER(email_biller) = ?', [strtolower($petugas->email)])
                    ->first();
                
                // Set data keterangan
                $petugas->berhasil_didata = $keterangan->berhasil_didata ?? 0;
                $petugas->tidak_ada_responden = $keterangan->tidak_ada_responden ?? 0;
                $petugas->responden_menolak = $keterangan->responden_menolak ?? 0;
                $petugas->meteran_tidak_ditemukan = $keterangan->meteran_tidak_ditemukan ?? 0;
                
                // Debug: log jika email tidak ditemukan (opsional)
                if (!$keterangan) {
                    \Log::info('Keterangan tidak ditemukan untuk email: ' . $petugas->email);
                }
                
                return $petugas;
            })
            ->sortBy('nama')
            ->values();

        // Total GC
        $totalOpen = $activeLayanan === 'prabayar' ? 0 : $petugases->sum('total_open');
        $totalSubmitted = $petugases->sum('total_submitted');
        $totalRejected = $petugases->sum('total_rejected');
        
        $grandTotal = $totalOpen + $totalSubmitted + $totalRejected;
        $totalPersentase = $grandTotal > 0 ? round(($totalSubmitted / $grandTotal) * 100, 2) : 0;
        $statusChartLabels = $activeLayanan === 'prabayar'
            ? ['Submitted', 'Rejected']
            : ['Open', 'Submitted', 'Rejected'];
        $statusChartValues = $activeLayanan === 'prabayar'
            ? [$totalSubmitted, $totalRejected]
            : [$totalOpen, $totalSubmitted, $totalRejected];
        $statusChartColors = $activeLayanan === 'prabayar'
            ? ['#3b82f6', '#f43f5e']
            : ['#94a3b8', '#3b82f6', '#f43f5e'];

        $lastUpdate = HasilGc::where('jenis_layanan', $activeLayanan)->latest('updated_at')->first()?->updated_at;

        // Total Keterangan Keseluruhan
        $totalBerhasilDidata = $petugases->sum('berhasil_didata');
        $totalTidakAdaResponden = $petugases->sum('tidak_ada_responden');
        $totalRespondenMenolak = $petugases->sum('responden_menolak');
        $totalMeteranTidakDitemukan = $petugases->sum('meteran_tidak_ditemukan');
        
        $totalKeseluruhan = $totalBerhasilDidata + $totalTidakAdaResponden + $totalRespondenMenolak + $totalMeteranTidakDitemukan;
        $persentaseBerhasil = $totalKeseluruhan > 0 ? round(($totalBerhasilDidata / $totalKeseluruhan) * 100, 2) : 0;

        return view('dashboard', compact(
            'petugases', 
            'totalOpen', 
            'totalSubmitted', 
            'totalRejected', 
            'totalPersentase', 
            'statusChartLabels',
            'statusChartValues',
            'statusChartColors',
            'lastUpdate',
            'totalBerhasilDidata',
            'totalTidakAdaResponden',
            'totalRespondenMenolak',
            'totalMeteranTidakDitemukan',
            'totalKeseluruhan',
            'persentaseBerhasil',
            'layananOptions',
            'activeLayanan',
            'activeLayananLabel',
            'activeTheme'
        ));
    }
}
