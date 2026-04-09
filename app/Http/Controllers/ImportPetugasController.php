<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Imports\PetugasImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportPetugasController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'file_petugas' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($request->password !== 'petugaspln2708') {
            return redirect()->back()->withErrors(['password' => 'Password import petugas salah.']);
        }

        try {
            Excel::import(new PetugasImport, $request->file('file_petugas'));
            return redirect()->back()->with('success', 'Data Petugas berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file_petugas' => 'Gagal import: ' . $e->getMessage()]);
        }
    }
}
