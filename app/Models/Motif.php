<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motif extends Model
{
    use HasFactory;

    protected $table = 'motifs';

    protected $fillable = ['direction_id', 'nom'];

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }
}