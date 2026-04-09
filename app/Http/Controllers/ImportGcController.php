<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Imports\GcImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportGcController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'file_gc' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($request->password !== 'gcpln2026') {
            return redirect()->back()->withErrors(['password' => 'Password import GC salah.']);
        }

        try {
            Excel::import(new GcImport, $request->file('file_gc'));
            return redirect()->back()->with('success', 'Data Hasil GC berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file_gc' => 'Gagal import: ' . $e->getMessage()]);
        }
    }
}
