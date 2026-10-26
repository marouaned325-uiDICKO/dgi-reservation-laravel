<?php

namespace App\Http\Controllers;

use App\Models\Localite;
use App\Models\Secteur;
use Illuminate\Http\Request;

class LocaliteController extends Controller
{
    public function index()
    {
        $localites = Localite::with('secteurs')->orderBy('nom')->get();
        return view('localites.index', compact('localites'));
    }

    public function create()
    {
        return view('localites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100|unique:localites,nom',
            'secteurs' => 'nullable|array',
            'secteurs.*' => 'nullable|string|max:150',
        ], [
            'nom.unique' => 'Cette localité existe déjà.',
        ]);

        $localite = Localite::create(['nom' => $validated['nom']]);

        foreach (($validated['secteurs'] ?? []) as $nomSecteur) {
            if (trim((string) $nomSecteur) !== '') {
                Secteur::create([
                    'nom' => $nomSecteur,
                    'localite_id' => $localite->id,
                ]);
            }
        }

        return redirect()->route('admin.localites.index')->with('success', 'Localité ajoutée avec succès.');
    }

    public function edit(Localite $localite)
    {
        $localite->load('secteurs');
        return view('localites.edit', compact('localite'));
    }

    public function update(Request $request, Localite $localite)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100|unique:localites,nom,' . $localite->id,
            'secteurs' => 'nullable|array',
            'nouveaux_secteurs' => 'nullable|array',
            'nouveaux_secteurs.*' => 'nullable|string|max:150',
        ], [
            'nom.unique' => 'Cette localité existe déjà.',
        ]);

        $localite->update(['nom' => $validated['nom']]);

        if (!empty($validated['secteurs'])) {
            foreach ($validated['secteurs'] as $secteurId => $nomSecteur) {
                Secteur::where('id', $secteurId)
                    ->where('localite_id', $localite->id)
                    ->update(['nom' => $nomSecteur]);
            }
        }

        if (!empty($validated['nouveaux_secteurs'])) {
            foreach ($validated['nouveaux_secteurs'] as $nomSecteur) {
                if (trim((string) $nomSecteur) !== '') {
                    Secteur::create([
                        'nom' => $nomSecteur,
                        'localite_id' => $localite->id,
                    ]);
                }
            }
        }

        return redirect()->route('admin.localites.index')->with('success', 'Localité modifiée avec succès.');
    }

    public function destroy(Localite $localite)
    {
        $localite->delete();
        return redirect()->route('admin.localites.index')->with('success', 'Localité supprimée avec succès.');
    }
}