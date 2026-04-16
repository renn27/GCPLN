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
            'file_keterangan' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($request->password !== 'gcpln2026') {
            return redirect()->back()->withErrors(['password' => 'Password import keterangan salah.']);
        }

        try {
            Excel::import(new KeteranganImport, $request->file('file_keterangan'));
            return redirect()->back()->with('success', 'Data Keterangan berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file_keterangan' => 'Gagal import: ' . $e->getMessage()]);
        }
    }
}