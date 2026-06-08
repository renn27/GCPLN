# Analisis & Pseudocode Algoritma — Aplikasi GCPLN

> **Aplikasi**: GCPLN — Sistem Monitoring Ground Check (GC) PLN  
> **Framework**: Laravel (PHP) + SQLite  
> **Library Utama**: Maatwebsite/Laravel-Excel (import & export)

---

## Gambaran Sistem

Aplikasi ini adalah sistem monitoring internal PLN untuk memantau progres **Ground Check (GC)** per petugas. Data dikelola melalui import file Excel dan ditampilkan dalam dashboard ringkasan.

### Relasi Antar Tabel (Entity Relationship)

```
PETUGAS (id, nama, email)
    │
    │ 1 ──── N
    ▼
RBM (id, kode_rbm, petugas_id)
    │
    │ 1 ──── 1
    ▼
HASIL_GC (id, rbm_id, open, submitted, rejected)

KETERANGAN (id, unitupi, unitap, unitup, email_biller,
            berhasil_didata, tidak_ada_responden,
            responden_menolak, meteran_tidak_ditemukan)
    │
    └──── terhubung ke PETUGAS melalui: email_biller = email (case-insensitive)
```

**Alur Data Singkat:**
```
Excel Petugas ──► [Import Petugas]  ──► tabel: petugas, rbms
Excel GC      ──► [Import GC]       ──► tabel: hasil_gcs
Excel Keterangan► [Import Keterangan]►  tabel: keterangan
                                             │
                                             ▼
                                    [Dashboard] ◄── JOIN via email
                                             │
                                             ▼
                                    [Export Rekap] ──► rekap_gc.xlsx
```

---

## Daftar Routes (Endpoint)

| Method | URL | Fitur |
|--------|-----|-------|
| `GET` | `/` | Dashboard utama |
| `POST` | `/import-petugas` | Upload Excel data petugas |
| `POST` | `/import-gc` | Upload Excel hasil GC |
| `POST` | `/import-keterangan` | Upload Excel keterangan |
| `GET` | `/export-rekap` | Download rekap Excel |

---

## FITUR 1 — Dashboard Monitoring

**File**: `DashboardController.php → index()`  
**Route**: `GET /`

### Pseudocode

```
FUNGSI dashboard_index():

    // ═══════════════════════════════════════════════
    // LANGKAH 1: Ambil semua data petugas beserta GC
    // ═══════════════════════════════════════════════
    petugases ← query Petugas dengan relasi (rbms → hasilGc)

    UNTUK SETIAP petugas DALAM petugases:

        // -- Hitung total GC dari semua RBM milik petugas --
        open      ← 0 jika prabayar; selain itu JUMLAH (rbm.hasilGc.open) untuk tiap rbm milik petugas
        submitted ← JUMLAH (rbm.hasilGc.submitted)  untuk tiap rbm milik petugas
        rejected  ← JUMLAH (rbm.hasilGc.rejected)   untuk tiap rbm milik petugas
        total     ← open + submitted + rejected

        JIKA total > 0 MAKA
            persentase ← BULATKAN((submitted / total) × 100, 2 desimal)
        SEBALIKNYA
            persentase ← 0
        AKHIR JIKA

        // -- Simpan ke properti objek petugas --
        petugas.total_open      ← open
        petugas.total_submitted ← submitted
        petugas.total_rejected  ← rejected
        petugas.persentase      ← persentase

        // -- Cari data keterangan berdasarkan email (case-insensitive) --
        keterangan ← cari di tabel Keterangan
                     WHERE LOWER(email_biller) = LOWER(petugas.email)
                     LIMIT 1

        JIKA keterangan DITEMUKAN MAKA
            petugas.berhasil_didata         ← keterangan.berhasil_didata
            petugas.tidak_ada_responden     ← keterangan.tidak_ada_responden
            petugas.responden_menolak       ← keterangan.responden_menolak
            petugas.meteran_tidak_ditemukan ← keterangan.meteran_tidak_ditemukan
        SEBALIKNYA
            petugas.berhasil_didata         ← 0
            petugas.tidak_ada_responden     ← 0
            petugas.responden_menolak       ← 0
            petugas.meteran_tidak_ditemukan ← 0
            LOG "Keterangan tidak ditemukan untuk email: " + petugas.email
        AKHIR JIKA

    AKHIR UNTUK

    // Urutkan daftar petugas berdasarkan nama A-Z
    petugases ← URUTKAN petugases berdasarkan nama ASC

    // ═══════════════════════════════════════════════
    // LANGKAH 2: Hitung Grand Total GC
    // ═══════════════════════════════════════════════
    totalOpen      ← 0 jika prabayar; selain itu JUMLAH (petugas.total_open) dari semua petugas
    totalSubmitted ← JUMLAH (petugas.total_submitted)  dari semua petugas
    totalRejected  ← JUMLAH (petugas.total_rejected)   dari semua petugas
    grandTotal     ← totalOpen + totalSubmitted + totalRejected

    JIKA grandTotal > 0 MAKA
        totalPersentase ← BULATKAN((totalSubmitted / grandTotal) × 100, 2 desimal)
    SEBALIKNYA
        totalPersentase ← 0
    AKHIR JIKA

    lastUpdate ← ambil updated_at dari HasilGc terbaru (ORDER BY updated_at DESC LIMIT 1)

    // ═══════════════════════════════════════════════
    // LANGKAH 3: Hitung Grand Total Keterangan
    // ═══════════════════════════════════════════════
    totalBerhasilDidata       ← JUMLAH (petugas.berhasil_didata)         dari semua petugas
    totalTidakAdaResponden    ← JUMLAH (petugas.tidak_ada_responden)     dari semua petugas
    totalRespondenMenolak     ← JUMLAH (petugas.responden_menolak)       dari semua petugas
    totalMeteranTidakDitemukan← JUMLAH (petugas.meteran_tidak_ditemukan) dari semua petugas

    totalKeseluruhan ← totalBerhasilDidata + totalTidakAdaResponden
                     + totalRespondenMenolak + totalMeteranTidakDitemukan

    JIKA totalKeseluruhan > 0 MAKA
        persentaseBerhasil ← BULATKAN((totalBerhasilDidata / totalKeseluruhan) × 100, 2 desimal)
    SEBALIKNYA
        persentaseBerhasil ← 0
    AKHIR JIKA

    // ═══════════════════════════════════════════════
    // LANGKAH 4: Kirim data ke tampilan
    // ═══════════════════════════════════════════════
    KEMBALIKAN view('dashboard') dengan data:
        petugases, totalOpen, totalSubmitted, totalRejected,
        totalPersentase, lastUpdate,
        totalBerhasilDidata, totalTidakAdaResponden,
        totalRespondenMenolak, totalMeteranTidakDitemukan,
        totalKeseluruhan, persentaseBerhasil

AKHIR FUNGSI
```

---

## FITUR 2 — Import Data Petugas

**File**: `ImportPetugasController.php` + `PetugasImport.php`  
**Route**: `POST /import-petugas`  
**Format Excel**: kolom `rbm`, `nama_petugas`, `email`

### Pseudocode

```
FUNGSI import_petugas(request):

    // ── VALIDASI INPUT ──────────────────────────────
    VALIDASI request:
        - 'password'     : wajib diisi
        - 'file_petugas' : wajib diisi, format hanya xlsx/xls/csv

    JIKA validasi GAGAL MAKA
        KEMBALIKAN redirect ke halaman sebelumnya dengan pesan error validasi
    AKHIR JIKA

    // ── VERIFIKASI PASSWORD ─────────────────────────
    JIKA request.password ≠ 'gcpln2026' MAKA
        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan error: "Password import petugas salah."
    AKHIR JIKA

    // ── PROSES IMPORT ───────────────────────────────
    COBA:
        JALANKAN PetugasImport dengan file = request.file_petugas

            // === Di dalam PetugasImport.collection(rows) ===
            MULAI TRANSAKSI DATABASE:

                UNTUK SETIAP baris (row) DALAM rows:

                    // Lewati baris yang tidak lengkap
                    JIKA row['rbm'] KOSONG ATAU row['nama_petugas'] KOSONG MAKA
                        LANJUT ke baris berikutnya
                    AKHIR JIKA

                    // Bersihkan email: lowercase + trim spasi
                    email ← LOWERCASE(TRIM(row['email']))

                    // Cari atau buat petugas baru (berdasarkan email unik)
                    petugas ← CARI di Petugas WHERE email = email
                    JIKA tidak ada MAKA
                        BUAT Petugas baru {nama: TRIM(row['nama_petugas']), email: email}
                    AKHIR JIKA

                    // Cari atau buat RBM baru (berdasarkan kode_rbm unik)
                    CARI di Rbm WHERE kode_rbm = TRIM(row['rbm'])
                    JIKA tidak ada MAKA
                        BUAT Rbm baru {kode_rbm: TRIM(row['rbm']), petugas_id: petugas.id}
                    AKHIR JIKA

                AKHIR UNTUK

            AKHIR TRANSAKSI
            // ============================================

        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan pesan sukses: "Data Petugas berhasil diimport."

    TANGKAP Exception sebagai e:
        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan error: "Gagal import: " + e.getMessage()
    AKHIR COBA

AKHIR FUNGSI
```

---

## FITUR 3 — Import Data Hasil GC

**File**: `ImportGcController.php` + `GcImport.php`  
**Route**: `POST /import-gc`  
**Format Excel Pascabayar**: kolom `rbm`, `open`, `submitted`, `rejected`  
**Format Excel Prabayar**: kolom `email_biller`, `submitted`, `rejected` (tanpa `rbm` dan `open`)

### Pseudocode

```
FUNGSI import_gc(request):

    // ── VALIDASI INPUT ──────────────────────────────
    VALIDASI request:
        - 'password' : wajib diisi
        - 'file_gc'  : wajib diisi, format hanya xlsx/xls/csv

    JIKA validasi GAGAL MAKA
        KEMBALIKAN redirect ke halaman sebelumnya dengan pesan error validasi
    AKHIR JIKA

    // ── VERIFIKASI PASSWORD ─────────────────────────
    JIKA request.password ≠ 'gcpln2026' MAKA
        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan error: "Password import GC salah."
    AKHIR JIKA

    // ── PROSES IMPORT ───────────────────────────────
    COBA:
        JALANKAN GcImport dengan file = request.file_gc

            // === Di dalam GcImport.collection(rows) ===

            // Cek apakah file kosong
            JIKA rows KOSONG MAKA
                LEMPAR Exception: "File Excel terlihat kosong."
            AKHIR JIKA

            // Validasi struktur kolom (cek baris pertama)
            firstRow ← rows[0]
            Untuk pascabayar, wajib ada kolom 'rbm', 'open', 'submitted', 'rejected'
            Untuk prabayar, wajib ada kolom 'email_biller', 'submitted', 'rejected'

            JIKA salah satu kolom wajib sesuai jenis_layanan TIDAK ADA di firstRow MAKA
                LEMPAR Exception: "Kolom wajib tidak ditemukan. Pastikan format kolom sesuai."
            AKHIR JIKA

            MULAI TRANSAKSI DATABASE:

                // Hapus semua data lama (REPLACE / fresh import)
                HAPUS semua record dari tabel HasilGc

                UNTUK SETIAP baris (row) DALAM rows:

                    // Lewati baris tanpa kode RBM
                    JIKA row['rbm'] TIDAK ADA MAKA
                        LANJUT ke baris berikutnya
                    AKHIR JIKA

                    // Cari RBM berdasarkan kode
                    rbm ← CARI di Rbm WHERE kode_rbm = row['rbm']

                    JIKA rbm TIDAK DITEMUKAN MAKA
                        LANJUT ke baris berikutnya  // abaikan RBM yang tidak terdaftar
                    AKHIR JIKA

                    // Simpan hasil GC
                    BUAT HasilGc baru:
                        rbm_id    ← rbm.id
                        open      ← 0 jika prabayar; selain itu INTEGER(row['open']) default 0
                        submitted ← INTEGER(row['submitted'])  default 0
                        rejected  ← INTEGER(row['rejected'])   default 0

                AKHIR UNTUK

            AKHIR TRANSAKSI
            // ============================================

        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan pesan sukses: "Data Hasil GC berhasil diimport."

    TANGKAP Exception sebagai e:
        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan error: "Gagal import: " + e.getMessage()
    AKHIR COBA

AKHIR FUNGSI
```

> [!WARNING]
> Import GC bersifat **REPLACE** — setiap kali import dijalankan, **seluruh data `hasil_gcs` lama akan dihapus** dan diganti dengan data baru dari file Excel.

---

## FITUR 4 — Import Data Keterangan

**File**: `ImportKeteranganController.php` + `KeteranganImport.php`  
**Route**: `POST /import-keterangan`  
**Format Excel**: kolom `unitupi`, `unitap`, `unitup`, `email_biller`, `1_berhasil_didata`, `2_tidak_ada_responden_...`, `3_responden_menolak`, `4_meteran_tidak_ditemukan`

### Pseudocode

```
FUNGSI import_keterangan(request):

    // ── VALIDASI INPUT ──────────────────────────────
    VALIDASI request:
        - 'password'         : wajib diisi
        - 'file_keterangan'  : wajib diisi, format hanya xlsx/xls/csv

    JIKA validasi GAGAL MAKA
        KEMBALIKAN redirect ke halaman sebelumnya dengan pesan error validasi
    AKHIR JIKA

    // ── VERIFIKASI PASSWORD ─────────────────────────
    JIKA request.password ≠ 'gcpln2026' MAKA
        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan error: "Password import keterangan salah."
    AKHIR JIKA

    // ── PROSES IMPORT ───────────────────────────────
    COBA:
        JALANKAN KeteranganImport dengan file = request.file_keterangan

            // === Di dalam KeteranganImport.collection(rows) ===

            // Cek apakah file kosong
            JIKA rows KOSONG MAKA
                LEMPAR Exception: "File Excel terlihat kosong."
            AKHIR JIKA

            // Validasi struktur kolom wajib
            firstRow ← rows[0]
            JIKA salah satu kolom berikut TIDAK ADA di firstRow:
                ('unitupi', 'unitap', 'unitup', 'email_biller',
                 '1_berhasil_didata',
                 '2_tidak_ada_responden_yang_dapat_memberi_jawabanrumah_kosong',
                 '3_responden_menolak', '4_meteran_tidak_ditemukan')
            MAKA
                LEMPAR Exception: "Kolom wajib tidak ditemukan. Pastikan format kolom sesuai."
            AKHIR JIKA

            MULAI TRANSAKSI DATABASE:

                // Hapus semua data lama (REPLACE / fresh import)
                HAPUS semua record dari tabel Keterangan

                UNTUK SETIAP baris (row) DALAM rows:

                    // Lewati baris tanpa unit UPI
                    JIKA row['unitupi'] TIDAK ADA MAKA
                        LANJUT ke baris berikutnya
                    AKHIR JIKA

                    // Bersihkan nilai unit (hapus karakter '[' dan ']')
                    unitupi ← TRIM(HAPUS('[',']' dari row['unitupi']))
                    unitap  ← TRIM(HAPUS('[',']' dari row['unitap']))
                    unitup  ← TRIM(HAPUS('[',']' dari row['unitup']))

                    // Bersihkan email: lowercase + trim
                    emailBiller ← LOWERCASE(TRIM(row['email_biller']))

                    // Simpan data keterangan
                    BUAT Keterangan baru:
                        unitupi                 ← unitupi
                        unitap                  ← unitap
                        unitup                  ← unitup
                        email_biller            ← emailBiller
                        berhasil_didata         ← INTEGER(row['1_berhasil_didata'])        default 0
                        tidak_ada_responden     ← INTEGER(row['2_tidak_ada_responden...']) default 0
                        responden_menolak       ← INTEGER(row['3_responden_menolak'])      default 0
                        meteran_tidak_ditemukan ← INTEGER(row['4_meteran_tidak_ditemukan'])default 0

                AKHIR UNTUK

            AKHIR TRANSAKSI
            // ============================================

        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan pesan sukses: "Data Keterangan berhasil diimport."

    TANGKAP Exception sebagai e:
        KEMBALIKAN redirect ke halaman sebelumnya
                   dengan error: "Gagal import: " + e.getMessage()
    AKHIR COBA

AKHIR FUNGSI
```

> [!WARNING]
> Import Keterangan juga bersifat **REPLACE** — seluruh data `keterangan` lama dihapus setiap kali import baru dijalankan.

---

## FITUR 5 — Export Rekap GC ke Excel

**File**: `ExportController.php` + `RekapExport.php`  
**Route**: `GET /export-rekap`  
**Output**: File `rekap_gc.xlsx`

### Pseudocode

```
FUNGSI export_rekap():

    JALANKAN RekapExport → hasilkan file Excel:

        // === Di dalam RekapExport ===

        // LANGKAH 1: Ambil semua petugas dengan relasi GC
        collection():
            petugases ← query Petugas dengan relasi (rbms → hasilGc)

            UNTUK SETIAP petugas DALAM petugases:
                open      ← 0 jika prabayar; selain itu JUMLAH (rbm.hasilGc.open) untuk tiap rbm
                submitted ← JUMLAH (rbm.hasilGc.submitted)  untuk tiap rbm
                rejected  ← JUMLAH (rbm.hasilGc.rejected)   untuk tiap rbm
                total     ← open + submitted + rejected

                JIKA total > 0 MAKA
                    persentase ← BULATKAN((submitted / total) × 100, 2 desimal)
                SEBALIKNYA
                    persentase ← 0
                AKHIR JIKA

                petugas.total_open      ← open
                petugas.total_submitted ← submitted
                petugas.total_rejected  ← rejected
                petugas.persentase      ← persentase

            AKHIR UNTUK

            KEMBALIKAN koleksi petugases

        // LANGKAH 2: Definisikan header kolom Excel
        headings():
            JIKA prabayar MAKA
                KEMBALIKAN ['Layanan', 'Nama Petugas', 'Submitted', 'Rejected', 'Persentase']
            SEBALIKNYA
                KEMBALIKAN ['Layanan', 'Nama Petugas', 'Open', 'Submitted', 'Rejected', 'Persentase']
            AKHIR JIKA

        // LANGKAH 3: Map setiap baris data ke array
        map(petugas):
            JIKA prabayar MAKA
                KEMBALIKAN [
                    'Prabayar',
                    petugas.nama,
                    petugas.total_submitted,
                    petugas.total_rejected,
                    petugas.persentase + '%'
                ]
            SEBALIKNYA
                KEMBALIKAN [
                    'Pascabayar',
                    petugas.nama,
                    petugas.total_open,
                    petugas.total_submitted,
                    petugas.total_rejected,
                    petugas.persentase + '%'
                ]
            AKHIR JIKA

    // Kirim file sebagai respons download
    KEMBALIKAN file Excel dengan nama 'rekap_gc.xlsx'

AKHIR FUNGSI
```

---

## Ringkasan Mekanisme Keamanan

| Fitur | Mekanisme Proteksi |
|-------|-------------------|
| Import Petugas | Password hardcode `gcpln2026` + validasi format file |
| Import GC | Password hardcode `gcpln2026` + validasi format file + validasi kolom |
| Import Keterangan | Password hardcode `gcpln2026` + validasi format file + validasi kolom |
| Export Rekap | Tidak ada proteksi (publik) |

---

## Catatan Penting Teknis

| Aspek | Keterangan |
|-------|-----------|
| **Pencocokan email** | Dilakukan secara **case-insensitive** menggunakan `LOWER()` di SQL agar data `petugas.email` dan `keterangan.email_biller` bisa cocok meski beda huruf besar/kecil |
| **Import GC & Keterangan** | Bersifat **full-replace**: data lama selalu dihapus dulu sebelum data baru dimasukkan |
| **Import Petugas** | Bersifat **upsert** (`firstOrCreate`): data lama tidak dihapus, hanya menambah yang belum ada |
| **Transaksi DB** | Semua proses import dibungkus dalam `DB::transaction()` untuk menjamin atomicity |
| **RBM tidak terdaftar** | Baris GC dengan kode RBM yang tidak ada di tabel `rbms` akan **diabaikan** |
