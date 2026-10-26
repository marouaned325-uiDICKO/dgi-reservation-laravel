<?php

namespace App\Http\Controllers;

use App\Models\Salle;

class SalleGalerieController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return $this->liste();
        }

        $salles = Salle::orderBy('nom')->get();
        return view('salles.galerie', compact('salles'));
    }

    public function liste()
    {
        $salles = Salle::orderBy('nom')->get();
        return view('salles.liste', compact('salles'));
    }

    public function show(Salle $salle)
    {
        return view('salles.show', compact('salle'));
    }
}