<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCPLN Dashboard</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tablesort@5.3.0/src/tablesort.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tablesort@5.3.0/src/sorts/tablesort.number.js"></script>
    <style>
       /* Sorting indicator - Simple & Visible */
th.sort-header {
    position: relative;
    cursor: pointer;
    user-select: none;
    padding-right: 28px !important;
}

th.sort-header:hover {
    background-color: #f8fafc;
}

/* Default - double arrow showing sort is available */
th.sort-header::after {
    content: "⇅";
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.3;
    font-size: 14px;
    color: #64748b;
    transition: opacity 0.15s ease;
}

th.sort-header:hover::after {
    opacity: 0.6;
}

/* Sorted ascending */
th.sort-header.sort-up::after {
    content: "↑";
    opacity: 1;
    color: #3b82f6;
    font-size: 14px;
    font-weight: 500;
}

/* Sorted descending */
th.sort-header.sort-down::after {
    content: "↓";
    opacity: 1;
    color: #3b82f6;
    font-size: 14px;
    font-weight: 500;
}
    </style>
</head>
<body class="bg-slate-50/50 text-slate-700 font-sans antialiased">

<div class="min-h-screen flex flex-col" x-data="{ showImportPetugas: false, showImportGc: false }">
    <!-- Navbar - Glassmorphism style -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-20 border-b border-slate-200/60 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left side -->
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo-bps.svg') }}" alt="BPS" class="h-7 w-auto">
                <div class="w-px h-6 bg-slate-300 mx-1"></div>
                <img src="{{ asset('images/logo-pln.png') }}" alt="PLN" class="h-6 w-auto">
                <div class="ml-2">
                    <p class="text-sm md:text-base font-bold text-blue-600 leading-tight">GC DTSEN - PLN OGAN ILIR</p>
                </div>
            </div>
            
            <!-- Right side -->
            <div class="flex items-center">
                <span class="text-xs text-slate-400 hidden md:block px-3 py-1.5 bg-slate-50 rounded-full border border-slate-200">
                    {{ now()->isoFormat('dddd, D MMMM YYYY') }}
                </span>
            </div>
        </div>
    </div>
</nav>

    <!-- Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <!-- Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Rekap GC DTSEN</h1>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    @if($lastUpdate)
                        <span class="hidden sm:inline-block w-1 h-1 rounded-full bg-slate-300"></span>
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Update: {{ $lastUpdate?->isoFormat('D MMM YYYY, HH:mm') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <button @click="showImportPetugas = true" class="group px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl shadow-sm text-sm font-medium hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Import Petugas</span>
                </button>
                <button @click="showImportGc = true" class="group px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl shadow-sm text-sm font-medium hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import GC</span>
                </button>
                <a href="{{ route('export.rekap') }}" class="group px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 border border-transparent text-white rounded-xl shadow-md shadow-green-200 text-sm font-medium hover:from-emerald-700 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Excel</span>
                </a>
            </div>
        </div>

        <!-- Alert messages with improved design -->
        @if(session('success'))
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-4 rounded-xl shadow-sm flex items-start gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 bg-rose-50 border border-rose-200 p-4 rounded-xl shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-5 h-5 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="flex-1">
                        <ul class="list-disc pl-4 text-sm text-rose-700 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Summary Cards - Redesigned with icons and better visual hierarchy -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total Open -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover-lift group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Open</p>
                        <p class="text-3xl font-bold text-slate-800 tracking-tight">{{ number_format($totalOpen) }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-slate-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Belum diproses
                </p>
            </div>
            
            <!-- Total Submitted -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover-lift group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Submitted</p>
                        <p class="text-3xl font-bold text-blue-600 tracking-tight">{{ number_format($totalSubmitted) }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Berhasil dikirim
                </p>
            </div>
            
            <!-- Total Rejected -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover-lift group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Rejected</p>
                        <p class="text-3xl font-bold text-rose-500 tracking-tight">{{ number_format($totalRejected) }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500 group-hover:bg-rose-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Ditolak / Error
                </p>
            </div>
            
            <!-- Persentase -->
            <div class="bg-gradient-to-br from-emerald-50 to-white rounded-2xl shadow-sm border border-emerald-200/80 p-5 hover-lift group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-emerald-600/70 uppercase tracking-wider mb-1">Persentase</p>
                        <p class="text-3xl font-bold text-emerald-700 tracking-tight">{{ $totalPersentase }}%</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700 group-hover:bg-emerald-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                </div>
                <div class="mt-2 w-full bg-emerald-100 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min($totalPersentase, 100) }}%"></div>
                </div>
                <p class="text-xs text-emerald-600/70 mt-2">Tingkat keberhasilan</p>
            </div>
        </div>

        <!-- Charts Section - Better spacing and containers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
            <!-- Overall Status Donut Chart -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 hover-lift">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-5 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
                    <h2 class="text-base font-semibold text-slate-800">Distribusi Status</h2>
                    <span class="ml-auto text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-full">Overall</span>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            
            <!-- Top 5 Petugas Bar Chart -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 hover-lift">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-5 bg-gradient-to-b from-blue-500 to-indigo-500 rounded-full"></div>
                    <h2 class="text-base font-semibold text-slate-800">Top 5 Petugas</h2>
                    <span class="ml-auto text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-full">Submitted Terbanyak</span>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="petugasChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table - Clean and modern -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden hover-lift">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
                    <h2 class="text-base font-semibold text-slate-800">Rekapitulasi per Petugas</h2>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="rekapTable" class="min-w-full divide-y divide-slate-100">
                    <thead>
    <tr class="bg-slate-50/80">
        <th scope="col" class="sort-header px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
            <span class="flex items-center gap-1">
                Nama Petugas
            </span>
        </th>
        <th scope="col" class="sort-header px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Open</th>
        <th scope="col" class="sort-header px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Submitted</th>
        <th scope="col" class="sort-header px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rejected</th>
        <th scope="col" class="sort-header px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Persentase</th>
    </tr>
</thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($petugases as $index => $petugas)
                            <tr class="hover:bg-slate-50/80 transition-all duration-150 group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-600 font-medium text-sm group-hover:scale-105 transition-transform">
                                            {{ strtoupper(substr($petugas->nama, 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-medium text-slate-800">{{ $petugas->nama }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">{{ number_format($petugas->total_open) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">{{ number_format($petugas->total_submitted) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-rose-500 font-medium">{{ number_format($petugas->total_rejected) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php 
                                        $colorClass = $petugas->persentase >= 80 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($petugas->persentase >= 50 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $colorClass }}">
                                        {{ $petugas->persentase }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-sm font-medium">Belum ada data</p>
                                        <p class="text-xs mt-1">Silakan import Petugas dan Hasil GC untuk memulai</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200/60 bg-white/50 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-400">
            GCPLN Monitoring Dashboard • {{ date('Y') }}
        </div>
    </footer>

    <!-- Modal Import Petugas - Redesigned -->
    <div x-show="showImportPetugas" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;" x-transition.opacity.duration.200ms>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showImportPetugas" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="showImportPetugas = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showImportPetugas" x-transition.scale.origin.top.duration.200ms class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <form action="{{ route('import.petugas') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-5 pt-5 pb-4 sm:p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-800">Import Data Petugas</h3>
                                <p class="text-xs text-slate-500">Upload file Excel dengan data petugas</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">PASSWORD IMPORT</label>
                                <input type="password" name="password" required class="block w-full border border-slate-200 rounded-xl p-2.5 text-sm text-slate-800 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" placeholder="Masukkan password...">
                                <p class="text-[10px] text-slate-400 mt-1">Gunakan password yang valid</p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">FILE EXCEL</label>
                                <div class="relative">
                                    <input type="file" name="file_petugas" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Kolom: RBM, Nama Petugas, Email
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50/80 px-5 py-4 sm:px-6 flex flex-row-reverse gap-2 border-t border-slate-200">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl text-sm font-medium hover:from-blue-700 hover:to-blue-600 shadow-md shadow-blue-200 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Import
                        </button>
                        <button type="button" @click="showImportPetugas = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import GC - Redesigned -->
    <div x-show="showImportGc" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;" x-transition.opacity.duration.200ms>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showImportGc" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="showImportGc = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showImportGc" x-transition.scale.origin.top.duration.200ms class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <form action="{{ route('import.gc') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-5 pt-5 pb-4 sm:p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-800">Import Hasil GC</h3>
                                <p class="text-xs text-slate-500">Upload file Excel hasil GC</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-rose-50/50 border border-rose-200 rounded-xl p-3.5">
                                <p class="text-xs text-rose-700 flex items-start gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span><strong class="font-semibold">Peringatan:</strong> Data GC yang sudah ada akan dihapus dan diganti dengan data baru dari file ini.</span>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">PASSWORD IMPORT</label>
                                <input type="password" name="password" required class="block w-full border border-slate-200 rounded-xl p-2.5 text-sm text-slate-800 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" placeholder="Masukkan password...">
                                <p class="text-[10px] text-slate-400 mt-1">Gunakan password yang valid</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">FILE EXCEL GC</label>
                                <div class="relative">
                                    <input type="file" name="file_gc" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 border border-slate-200 rounded-xl p-1.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Kolom: RBM, OPEN, SUBMITTED, REJECTED
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50/80 px-5 py-4 sm:px-6 flex flex-row-reverse gap-2 border-t border-slate-200">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-amber-600 to-amber-500 text-white rounded-xl text-sm font-medium hover:from-amber-700 hover:to-amber-600 shadow-md shadow-amber-200 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Import & Ganti
                        </button>
                        <button type="button" @click="showImportGc = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Tablesort
    const table = document.getElementById('rekapTable');
    if (table) {
        const sorter = new Tablesort(table, {
            descending: false
        });
        
        // Add click handler to show sorting direction
        table.querySelectorAll('th.sort-header').forEach(th => {
            th.addEventListener('click', function() {
                // Remove sort classes from all headers
                table.querySelectorAll('th.sort-header').forEach(h => {
                    h.classList.remove('sort-up', 'sort-down');
                });
                
                // Add appropriate class based on sort direction
                setTimeout(() => {
                    const isDescending = this.getAttribute('aria-sort') === 'descending';
                    if (isDescending) {
                        this.classList.add('sort-down');
                    } else {
                        this.classList.add('sort-up');
                    }
                }, 10);
            });
        });
    }

        // Charts configuration
        const totalOpen = {{ $totalOpen }};
        const totalSubmitted = {{ $totalSubmitted }};
        const totalRejected = {{ $totalRejected }};
        
        // Pass complete data securely to Javascript
        const rawPetugases = @json($petugases);
        
        // Donut Chart - with improved styling
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'Submitted', 'Rejected'],
                datasets: [{
                    data: [totalOpen, totalSubmitted, totalRejected],
                    backgroundColor: ['#94a3b8', '#3b82f6', '#f43f5e'],
                    borderWidth: 0,
                    borderRadius: 6,
                    spacing: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 16,
                            font: { size: 12, family: 'Inter' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                    }
                }
            }
        });

        // Bar Chart Data Prep
        const top5 = rawPetugases
            .sort((a,b) => b.total_submitted - a.total_submitted)
            .slice(0, 5);

        const ctxPetugas = document.getElementById('petugasChart').getContext('2d');
        new Chart(ctxPetugas, {
            type: 'bar',
            data: {
                labels: top5.map(p => p.nama),
                datasets: [{
                    label: 'Total Submitted',
                    data: top5.map(p => p.total_submitted),
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    barPercentage: 0.65,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: '#e2e8f0', drawBorder: false },
                        ticks: { stepSize: 1, font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, maxRotation: 25 }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                    }
                }
            }
        });
    });
</script>

</body>
</html>