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
}
