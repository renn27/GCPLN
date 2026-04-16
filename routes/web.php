<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportPetugasController;
use App\Http\Controllers\ImportGcController;
use App\Http\Controllers\ImportKeteranganController;
use App\Http\Controllers\ExportController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/import-petugas', [ImportPetugasController::class, 'store'])->name('import.petugas');
Route::post('/import-keterangan', [ImportKeteranganController::class, 'store'])->name('import.keterangan');
Route::post('/import-gc', [ImportGcController::class, 'store'])->name('import.gc');
Route::get('/export-rekap', [ExportController::class, 'export'])->name('export.rekap');
