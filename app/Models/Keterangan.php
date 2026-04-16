<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keterangan extends Model
{
    protected $fillable = [
        'unitupi',
        'unitap',
        'unitup',
        'berhasil_didata',
        'tidak_ada_responden',
        'responden_menolak',
        'meteran_tidak_ditemukan'
    ];

    protected $table = 'keterangan';
}