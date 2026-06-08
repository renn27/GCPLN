<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exports\RekapExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $jenisLayanan = in_array($request->query('layanan'), ['pascabayar', 'prabayar'], true)
            ? $request->query('layanan')
            : 'pascabayar';

        return Excel::download(new RekapExport($jenisLayanan), 'rekap_gc_' . $jenisLayanan . '.xlsx');
    }
}
