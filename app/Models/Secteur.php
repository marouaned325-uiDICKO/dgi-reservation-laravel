<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Secteur extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'localite_id'];

    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }
}