<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $fillable = ['nama', 'email'];

    public function rbms()
    {
        return $this->hasMany(Rbm::class);
    }
    
    // UPDATE RELASI - Gunakan kondisi untuk case-insensitive
    public function keterangan()
    {
        return $this->hasOne(Keterangan::class, 'email_biller', 'email')
                    ->whereRaw('LOWER(email_biller) = LOWER(?)', [$this->email]);
    }
    
    // TAMBAHKAN ACCESSOR UNTUK MENDAPATKAN DATA KETERANGAN
    public function getKeteranganDataAttribute()
    {
        // Cari keterangan berdasarkan email (case-insensitive)
        return Keterangan::whereRaw('LOWER(email_biller) = ?', [strtolower($this->email)])->first();
    }
}