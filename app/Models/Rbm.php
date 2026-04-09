<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rbm extends Model
{
    protected $fillable = ['kode_rbm', 'petugas_id'];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class);
    }

    public function hasilGc()
    {
        return $this->hasOne(HasilGc::class);
    }
}
