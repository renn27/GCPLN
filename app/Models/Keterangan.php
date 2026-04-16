<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keterangan extends Model
{
    protected $fillable = [
        'unitupi',
        'unitap',
        'unitup',
        'email_biller',  // TAMBAHKAN INI
        'berhasil_didata',
        'tidak_ada_responden',
        'responden_menolak',
        'meteran_tidak_ditemukan'
    ];

    protected $table = 'keterangan';
    
    // TAMBAHKAN RELASI KE PETUGAS
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'email_biller', 'email');
    }
}