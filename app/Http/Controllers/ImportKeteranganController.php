<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\KeteranganImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportKeteranganController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'jenis_layanan' => 'required|in:pascabayar,prabayar',
            'file_keterangan' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($request->password !== 'gcpln2026') {
            return redirect()->back()->withErrors(['password' => 'Password import keterangan salah.']);
        }

        try {
            Excel::import(new KeteranganImport($request->jenis_layanan), $request->file('file_keterangan'));
            return redirect()->back()->with('success', 'Data Keterangan ' . $this->labelLayanan($request->jenis_layanan) . ' berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file_keterangan' => 'Gagal import: ' . $e->getMessage()]);
        }
    }

    private function labelLayanan(string $jenisLayanan): string
    {
        return $jenisLayanan === 'prabayar' ? 'Prabayar' : 'Pascabayar';
    }
}
