<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    public function secteurs()
    {
        return $this->hasMany(Secteur::class);
    }
}