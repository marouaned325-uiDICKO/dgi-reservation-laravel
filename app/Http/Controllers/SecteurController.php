<?php

namespace App\Http\Controllers;

use App\Models\Localite;
use App\Models\Secteur;
use Illuminate\Http\Request;

class SecteurController extends Controller
{
    public function store(Request $request, Localite $localite)
    {
        $request->validate([
            'nom' => 'required|string|max:150',
        ]);

        Secteur::create([
            'nom' => $request->nom,
            'localite_id' => $localite->id,
        ]);

        return redirect()->route('admin.localites.index')->with('success', 'Secteur ajouté avec succès.');
    }

    public function update(Request $request, Secteur $secteur)
    {
        $request->validate([
            'nom' => 'required|string|max:150',
        ]);

        $secteur->nom = $request->nom;
        $secteur->save();

        return redirect()->route('admin.localites.index')->with('success', 'Secteur modifié avec succès.');
    }

    public function destroy(Secteur $secteur)
    {
        $secteur->delete();
        return redirect()->route('admin.localites.index')->with('success', 'Secteur supprimé avec succès.');
    }
}