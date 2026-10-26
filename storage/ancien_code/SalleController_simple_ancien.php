<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Equipement;
use App\Models\Localite;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalleController extends Controller
{
    /** Un non-administrateur doit être rattaché à une direction. */
    private function verifierAffectation(): void
    {
        $u = auth()->user();
        abort_if($u->role !== 'admin' && ! $u->direction_id, 403, "Votre compte n'est rattaché à aucune direction.");
    }

    /** Directions que l'utilisateur connecté peut choisir pour une salle. */
    private function directionsAccessibles()
    {
        $u = auth()->user();
        $query = Direction::orderBy('nom_direction');

        if ($u->role !== 'admin') {
            $query->where('id', $u->direction_id);
        }

        return $query->get();
    }

    /** Une salle d'une autre direction ne peut être ni vue, ni modifiée, ni supprimée. */
    private function autoriser(Salle $salle): void
    {
        $u = auth()->user();

        abort_unless(
            $u->role === 'admin' || ($u->direction_id && (int) $salle->direction_id === (int) $u->direction_id),
            403,
            "Cette salle n'appartient pas à votre direction."
        );
    }

    

    private function regles(): array
    {
        $estAdmin = auth()->user()->role === 'admin';

        return [
            'direction_id' => $estAdmin ? 'required|exists:directions,id' : 'nullable',
            'nom' => 'required|string|max:100',
            'localite' => 'nullable|string',
            'secteur' => 'nullable|string',
            'statut' => 'required|in:disponible,indisponible,maintenance',
            'capacite' => 'required|integer|min:1',
            'equipement' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ];
    }

    /**
     * Catalogue d'équipements pour les formulaires de salle.
     *
     * - Gestionnaire : [direction_id => ['Écran', 'Wi-Fi', ...]] limité à sa direction.
     * - Administrateur : en plus, la clé '*' contient la liste fusionnée de toutes les
     *   directions. Les équipements de même nom (sans tenir compte des majuscules)
     *   créés dans plusieurs directions n'apparaissent qu'une seule fois.
     */
    private function catalogueEquipements($directions): array
    {
        $equipements = Equipement::whereIn('direction_id', $directions->pluck('id'))
            ->orderBy('nom')
            ->get();

        $catalogue = $equipements
            ->groupBy('direction_id')
            ->map(fn ($liste) => $liste->pluck('nom')->values()->all())
            ->all();

        if (auth()->user()->role === 'admin') {
            $catalogue['*'] = $equipements
                ->unique(fn ($e) => mb_strtolower(trim($e->nom)))
                ->pluck('nom')
                ->values()
                ->all();
        }

        return $catalogue;
    }

    private const MESSAGES = [
        'direction_id.required' => 'Choisissez la direction de cette salle.',
    ];

    public function index()
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $query = Salle::with('direction')->orderBy('nom');

        if ($u->role !== 'admin') {
            $query->where('direction_id', $u->direction_id);
        }

        $salles = $query->get();

        return view('salles.index', compact('salles'));
    }

    public function create()
    {
        $this->verifierAffectation();

        $localites = Localite::with('secteurs')->orderBy('nom')->get();
        $directions = $this->directionsAccessibles();
        $catalogue = $this->catalogueEquipements($directions);

        return view('salles.create', compact('localites', 'directions', 'catalogue'));
    }

    public function store(Request $request)
    {
        $this->verifierAffectation();

        $request->merge([
            'equipement' => implode(', ', $request->input('equipements', [])),
        ]);

        $validated = $request->validate($this->regles(), self::MESSAGES);

        // Hors administrateur : la salle est toujours créée dans sa propre direction
        if (auth()->user()->role !== 'admin') {
            $validated['direction_id'] = auth()->user()->direction_id;
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('salles', 'public');
        }

        Salle::create($validated);

        return redirect()->route('admin.salles.index')->with('success', 'Salle ajoutée avec succès.');
    }

    public function edit(Salle $salle)
    {
        $this->verifierAffectation();
        $this->autoriser($salle);

        $localites = Localite::with('secteurs')->orderBy('nom')->get();
        $directions = $this->directionsAccessibles();
        $catalogue = $this->catalogueEquipements($directions);

        return view('salles.edit', compact('salle', 'localites', 'directions', 'catalogue'));
    }

    public function update(Request $request, Salle $salle)
    {
        $this->verifierAffectation();
        $this->autoriser($salle);

        $request->merge([
            'equipement' => implode(', ', $request->input('equipements', [])),
        ]);

        $validated = $request->validate($this->regles(), self::MESSAGES);

        // Hors administrateur : la direction de la salle ne peut pas changer
        if (auth()->user()->role !== 'admin') {
            $validated['direction_id'] = $salle->direction_id;
        }

        if ($request->hasFile('photo')) {
            if ($salle->photo) {
                Storage::disk('public')->delete($salle->photo);
            }
            $validated['photo'] = $request->file('photo')->store('salles', 'public');
        }

        $salle->update($validated);

        return redirect()->route('admin.salles.index')->with('success', 'Salle modifiée avec succès.');
    }

    public function destroy(Salle $salle)
    {
        $this->verifierAffectation();
        $this->autoriser($salle);

        // Supprime les demandes en attente liées à cette salle (plus la peine de les traiter)
        $demandesEnAttente = $salle->reservations()->where('statut', 'en_attente')->get();

        foreach ($demandesEnAttente as $demande) {
            \App\Models\Notification::where('reservation_id', $demande->id)->delete();
            $demande->delete();
        }

        if ($salle->photo) {
            Storage::disk('public')->delete($salle->photo);
        }

        $salle->delete();

        return redirect()->route('admin.salles.index')->with('success', 'Salle supprimée avec succès.');
    }
}