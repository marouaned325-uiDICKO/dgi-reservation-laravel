<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Motif;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MotifController extends Controller
{
    /** Un non-administrateur doit être rattaché à une direction. */
    private function verifierAffectation(): void
    {
        $u = auth()->user();
        abort_if($u->role !== 'admin' && ! $u->direction_id, 403, "Votre compte n'est rattaché à aucune direction.");
    }

    private function directionsAccessibles()
    {
        $u = auth()->user();
        $query = Direction::orderBy('nom_direction');

        if ($u->role !== 'admin') {
            $query->where('id', $u->direction_id);
        }

        return $query->get();
    }

    /** Un motif d'une autre direction ne peut être ni modifié, ni supprimé. */
    private function autoriser(Motif $motif): void
    {
        $u = auth()->user();

        abort_unless(
            $u->role === 'admin' || ($u->direction_id && (int) $motif->direction_id === (int) $u->direction_id),
            403,
            "Ce motif n'appartient pas à votre direction."
        );
    }

    public function index()
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $query = Motif::with('direction')->orderBy('direction_id')->orderBy('nom');

        if ($u->role !== 'admin') {
            $query->where('direction_id', $u->direction_id);
        }

        $motifs = $query->get();
        $directions = $this->directionsAccessibles();

        return view('motifs.index', compact('motifs', 'directions'));
    }

    public function store(Request $request)
    {
        $this->verifierAffectation();
        $u = auth()->user();

        // Hors administrateur : toujours dans sa propre direction
        if ($u->role !== 'admin') {
            $request->merge(['direction_id' => $u->direction_id]);
        }

        $validated = $request->validate([
            'direction_id' => 'required|exists:directions,id',
            'noms' => 'required|string|max:1000',
        ], [
            'direction_id.required' => 'Choisissez la direction.',
            'direction_id.exists' => "Cette direction n'existe pas.",
            'noms.required' => 'Saisissez au moins un motif.',
        ]);

        $noms = collect(preg_split('/[,;\n]+/', $validated['noms']))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique(fn ($n) => mb_strtolower($n))
            ->values();

        if ($noms->isEmpty()) {
            return back()->withErrors(['noms' => 'Saisissez au moins un motif.'])->withInput();
        }

        if ($noms->contains(fn ($n) => mb_strlen($n) > 100)) {
            return back()->withErrors(['noms' => 'Un motif ne doit pas dépasser 100 caractères.'])->withInput();
        }

        $existants = Motif::where('direction_id', $validated['direction_id'])
            ->pluck('nom')
            ->map(fn ($n) => mb_strtolower($n))
            ->all();

        $nouveaux = $noms->reject(fn ($n) => in_array(mb_strtolower($n), $existants, true));

        if ($nouveaux->isEmpty()) {
            return back()->withErrors(['noms' => 'Ce motif existe déjà dans cette direction.'])->withInput();
        }

        foreach ($nouveaux as $nom) {
            Motif::create([
                'direction_id' => $validated['direction_id'],
                'nom' => $nom,
            ]);
        }

        return redirect()->route('admin.motifs.index')
            ->with('success', $nouveaux->count() > 1 ? 'Motifs ajoutés avec succès.' : 'Motif ajouté avec succès.');
    }

    public function update(Request $request, Motif $motif)
    {
        $this->verifierAffectation();
        $this->autoriser($motif);

        $validated = $request->validate([
            'nom' => [
                'required', 'string', 'max:100', 'not_regex:/[,;]/',
                Rule::unique('motifs', 'nom')
                    ->where('direction_id', $motif->direction_id)
                    ->ignore($motif->id),
            ],
        ], [
            'nom.required' => 'Le nom du motif est obligatoire.',
            'nom.unique' => 'Ce motif existe déjà dans cette direction.',
            'nom.not_regex' => 'Le nom ne doit pas contenir de virgule ni de point-virgule.',
        ]);

        $motif->update(['nom' => trim($validated['nom'])]);

        return redirect()->route('admin.motifs.index')
            ->with('success', 'Motif modifié avec succès.');
    }

    public function destroy(Motif $motif)
    {
        $this->verifierAffectation();
        $this->autoriser($motif);

        // Les réservations déjà faites gardent leur motif (il est enregistré en texte)
        $motif->delete();

        return redirect()->route('admin.motifs.index')
            ->with('success', 'Motif supprimé avec succès.');
    }
}