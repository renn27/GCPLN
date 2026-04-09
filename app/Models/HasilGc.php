<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilGc extends Model
{
    protected $fillable = ['rbm_id', 'open', 'submitted', 'rejected'];

    public function rbm()
    {
        return $this->belongsTo(Rbm::class);
    }
}
