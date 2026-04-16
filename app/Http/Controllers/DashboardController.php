<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Petugas;
use App\Models\HasilGc;
use App\Models\Keterangan;

class DashboardController extends Controller
{
    public function index()
    {
        $petugases = Petugas::with(['rbms.hasilGc'])
            ->get()
            ->map(function($petugas) {
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
            })
            ->sortBy('nama') // Tambahkan ini untuk mengurutkan ascending berdasarkan nama
            ->values(); // Reset index array

        $totalOpen = $petugases->sum('total_open');
        $totalSubmitted = $petugases->sum('total_submitted');
        $totalRejected = $petugases->sum('total_rejected');
        
        $grandTotal = $totalOpen + $totalSubmitted + $totalRejected;
        $totalPersentase = $grandTotal > 0 ? round(($totalSubmitted / $grandTotal) * 100, 2) : 0;

        $lastUpdate = HasilGc::latest('updated_at')->first()?->updated_at;

        // Data Keterangan
        $keterangan = Keterangan::first();
        
        $totalBerhasilDidata = $keterangan->berhasil_didata ?? 0;
        $totalTidakAdaResponden = $keterangan->tidak_ada_responden ?? 0;
        $totalRespondenMenolak = $keterangan->responden_menolak ?? 0;
        $totalMeteranTidakDitemukan = $keterangan->meteran_tidak_ditemukan ?? 0;
        
        $totalKeseluruhan = $totalBerhasilDidata + $totalTidakAdaResponden + $totalRespondenMenolak + $totalMeteranTidakDitemukan;
        $persentaseBerhasil = $totalKeseluruhan > 0 ? round(($totalBerhasilDidata / $totalKeseluruhan) * 100, 2) : 0;

        return view('dashboard', compact(
            'petugases', 
            'totalOpen', 
            'totalSubmitted', 
            'totalRejected', 
            'totalPersentase', 
            'lastUpdate',
            'totalBerhasilDidata',
            'totalTidakAdaResponden',
            'totalRespondenMenolak',
            'totalMeteranTidakDitemukan',
            'totalKeseluruhan',
            'persentaseBerhasil'
        ));
    }
}