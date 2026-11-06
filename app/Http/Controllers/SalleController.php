<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Equipement;
use App\Models\Localite;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SalleController extends Controller
{
    /** Un compte autre qu'Admin doit obligatoirement être rattaché à une direction. */
    private function verifierAffectation(): void
    {
        $u = auth()->user();
        abort_if($u->role !== 'admin' && ! $u->direction_id, 403, "Votre compte n'est rattaché à aucune direction.");
    }

    /** Un gestionnaire ne gère que les salles de sa direction ; l'administrateur gère toutes les salles. */
    private function autoriser(Salle $salle): void
    {
        $u = auth()->user();
        abort_unless(
            $u->role === 'admin' || (int) $salle->direction_id === (int) $u->direction_id,
            403,
            "Vous ne pouvez pas gérer cette salle."
        );
    }

    /** Directions que l'utilisateur connecté a le droit de choisir. */
    private function directionsAccessibles()
    {
        $u = auth()->user();
        $query = Direction::orderBy('nom_direction');

        if ($u->role !== 'admin') {
            $query->where('id', $u->direction_id);
        }

        return $query->get();
    }

    /**
     * Catalogue des équipements pour la liste déroulante du formulaire :
     *  - clé "*"            : tous les équipements (administrateur)
     *  - clé <direction_id> : équipements de cette direction (gestionnaire)
     */
    private function catalogueEquipements(): array
    {
        $tous = [];
        $parDirection = [];

        foreach (Equipement::orderBy('nom')->get() as $e) {
            $nom = trim((string) $e->nom);
            if ($nom === '') {
                continue;
            }
            $tous[] = $nom;
            $parDirection[(string) $e->direction_id][] = $nom;
        }

        $catalogue = ['*' => array_values(array_unique($tous))];

        foreach ($parDirection as $directionId => $noms) {
            $catalogue[$directionId] = array_values(array_unique($noms));
        }

        return $catalogue;
    }
    /** Règles de validation communes à l'ajout et à la modification. */
    private function valider(Request $request, ?Salle $salle = null): array
    {
        $u = auth()->user();

        // Un compte non-admin ne peut travailler que dans sa propre direction
        $directionId = $u->role === 'admin' ? $request->input('direction_id') : $u->direction_id;
        $request->merge(['direction_id' => $directionId]);

        $photoObligatoire = ! $salle || ! $salle->photo;

        return $request->validate([
            'direction_id' => ['required', 'exists:directions,id'],
            'nom' => [
                'required', 'string', 'max:255',
                Rule::unique('salles', 'nom')
                    ->where('direction_id', $directionId)
                    ->ignore($salle?->id),
            ],
            'localite' => ['nullable', 'string', 'max:255'],
            'capacite' => ['required', 'integer', 'min:1'],
            'statut' => ['required', Rule::in(['disponible', 'indisponible', 'maintenance'])],
            'equipements' => ['nullable', 'array'],
            'equipements.*' => ['string', 'max:100'],
            'photo' => [$photoObligatoire ? 'required' : 'nullable', 'image', 'max:2048'],
        ], [
            'direction_id.required' => 'Veuillez choisir une direction.',
            'direction_id.exists' => 'Cette direction est invalide.',
            'nom.required' => 'Le nom de la salle est obligatoire.',
            'nom.unique' => 'Une salle portant ce nom existe déjà dans cette direction.',
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être supérieure à 0.',
            'statut.required' => 'Veuillez sélectionner un statut.',
            'statut.in' => 'Le statut sélectionné est invalide.',
            'photo.required' => 'La photo de la salle est obligatoire.',
            'photo.image' => 'Le fichier doit être une image (JPG, PNG...).',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
        ]);
    }

    /** Transforme la liste cochée en texte enregistré dans la colonne "equipement". */
    private function equipementsEnTexte(array $liste): ?string
    {
        $liste = collect($liste)->map(fn ($e) => trim($e))->filter()->unique()->values();

        return $liste->isEmpty() ? null : $liste->implode(', ');
    }

    public function index()
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $query = Salle::query()->orderBy('nom');

        if (method_exists(Salle::class, 'direction')) {
            $query->with('direction');
        }

        if ($u->role !== 'admin') {
            $query->where('direction_id', $u->direction_id);
        }

        $salles = $query->get();

        return view('salles.index', compact('salles'));
    }

    public function create()
    {
        $this->verifierAffectation();

        return view('salles.create', [
            'directions' => $this->directionsAccessibles(),
            'localites' => Localite::orderBy('nom')->get(),
            'catalogue' => $this->catalogueEquipements(),
        ]);
    }

    public function store(Request $request)
    {
        $this->verifierAffectation();
        $validated = $this->valider($request);

        $salle = new Salle();
        $salle->forceFill([
            'direction_id' => $validated['direction_id'],
            'nom' => $validated['nom'],
            'localite' => $validated['localite'] ?? null,
            'capacite' => $validated['capacite'],
            'statut' => $validated['statut'],
            'equipement' => $this->equipementsEnTexte($validated['equipements'] ?? []),
            'photo' => $request->file('photo')->store('salles', 'public'),
        ])->save();

        return redirect()
            ->route('admin.salles.index')
            ->with('success', 'Salle ajoutée avec succès.');
    }

    public function edit(Salle $salle)
    {
        $this->verifierAffectation();
        $this->autoriser($salle);

        return view('salles.edit', [
            'salle' => $salle,
            'directions' => $this->directionsAccessibles(),
            'localites' => Localite::orderBy('nom')->get(),
            'catalogue' => $this->catalogueEquipements(),
        ]);
    }

    public function update(Request $request, Salle $salle)
    {
        $this->verifierAffectation();
        $this->autoriser($salle);
        $validated = $this->valider($request, $salle);

        $donnees = [
            'direction_id' => $validated['direction_id'],
            'nom' => $validated['nom'],
            'localite' => $validated['localite'] ?? null,
            'capacite' => $validated['capacite'],
            'statut' => $validated['statut'],
            'equipement' => $this->equipementsEnTexte($validated['equipements'] ?? []),
        ];

        // Nouvelle photo : on remplace l'ancienne
        if ($request->hasFile('photo')) {
            if ($salle->photo) {
                Storage::disk('public')->delete($salle->photo);
            }
            $donnees['photo'] = $request->file('photo')->store('salles', 'public');
        }

        $salle->forceFill($donnees)->save();

        return redirect()
            ->route('admin.salles.index')
            ->with('success', 'Salle modifiée avec succès.');
    }

    public function destroy(Salle $salle)
    {
        $this->verifierAffectation();
        $this->autoriser($salle);

        // Empêcher la suppression d'une salle ayant des réservations.
        if ($salle->reservations()->exists()) {
            return redirect()
                ->route('admin.salles.index')
                ->with('error', 'Cette salle ne peut pas être supprimée car elle possède des réservations.');
        }

        if ($salle->photo) {
            Storage::disk('public')->delete($salle->photo);
        }

        $salle->delete();

        return redirect()
            ->route('admin.salles.index')
            ->with('success', 'Salle supprimée avec succès.');
    }
}