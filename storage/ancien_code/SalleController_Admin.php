<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use Illuminate\Http\Request;

class SalleController extends Controller
{
    /**
     * Afficher la liste des salles.
     */
    public function index()
    {
        $salles = Salle::orderBy('nom')->get();

        return view('salles.index', compact('salles'));
    }

    /**
     * Afficher le formulaire d'ajout.
     */
    public function create()
    {
        return view('salles.create');
    }

    /**
     * Enregistrer une nouvelle salle.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'localite' => 'nullable|string|max:255',
            'statut' => 'required|in:disponible,indisponible,maintenance',
            'capacite' => 'required|integer|min:1',
            'equipement' => 'nullable|string',
        ], [
            'nom.required' => 'Le nom de la salle est obligatoire.',
            'statut.required' => 'Veuillez sélectionner un statut.',
            'statut.in' => 'Le statut sélectionné est invalide.',
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être supérieure à 0.',
        ]);

        Salle::create($validated);

        return redirect()
            ->route('salles.index')
            ->with('success', 'La salle a été ajoutée avec succès.');
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(Salle $salle)
    {
        return view('salles.edit', compact('salle'));
    }

    /**
     * Modifier une salle.
     */
    public function update(Request $request, Salle $salle)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'localite' => 'nullable|string|max:255',
            'statut' => 'required|in:disponible,indisponible,maintenance',
            'capacite' => 'required|integer|min:1',
            'equipement' => 'nullable|string',
        ], [
            'nom.required' => 'Le nom de la salle est obligatoire.',
            'statut.required' => 'Veuillez sélectionner un statut.',
            'statut.in' => 'Le statut sélectionné est invalide.',
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être supérieure à 0.',
        ]);

        $salle->update($validated);

        return redirect()
            ->route('salles.index')
            ->with('success', 'La salle a été modifiée avec succès.');
    }

    /**
     * Supprimer une salle.
     */
    public function destroy(Salle $salle)
    {
        // Empêcher la suppression d'une salle ayant des réservations.
        if ($salle->reservations()->exists()) {
            return redirect()
                ->route('salles.index')
                ->with('error', 'Cette salle ne peut pas être supprimée car elle possède des réservations.');
        }

        $salle->delete();

        return redirect()
            ->route('salles.index')
            ->with('success', 'La salle a été supprimée avec succès.');
    }
}