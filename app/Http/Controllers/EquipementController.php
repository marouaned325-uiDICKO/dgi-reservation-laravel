<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Equipement;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipementController extends Controller
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

    /** Un équipement d'une autre direction ne peut être ni modifié, ni supprimé. */
    private function autoriser(Equipement $equipement): void
    {
        $u = auth()->user();

        abort_unless(
            $u->role === 'admin' || ($u->direction_id && (int) $equipement->direction_id === (int) $u->direction_id),
            403,
            "Cet équipement n'appartient pas à votre direction."
        );
    }

    /** "A, B; C" => ['A', 'B', 'C'] */
    private function liste(?string $texte): array
    {
        return collect(preg_split('/[,;\n]+/', (string) $texte))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();
    }

    /** Salles de la direction qui utilisent cet équipement. */
    private function sallesUtilisant(Equipement $equipement)
    {
        return Salle::where('direction_id', $equipement->direction_id)
            ->whereNotNull('equipement')
            ->get()
            ->filter(fn ($s) => in_array($equipement->nom, $this->liste($s->equipement), true))
            ->values();
    }

    public function index()
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $query = Equipement::with('direction')->orderBy('direction_id')->orderBy('nom');
        $sallesQuery = Salle::whereNotNull('equipement');

        if ($u->role !== 'admin') {
            $query->where('direction_id', $u->direction_id);
            $sallesQuery->where('direction_id', $u->direction_id);
        }

        $equipements = $query->get();

        // Nombre de salles qui utilisent chaque équipement
        $usage = [];
        foreach ($sallesQuery->get(['direction_id', 'equipement']) as $salle) {
            foreach ($this->liste($salle->equipement) as $nom) {
                $usage[$salle->direction_id][$nom] = ($usage[$salle->direction_id][$nom] ?? 0) + 1;
            }
        }

        foreach ($equipements as $e) {
            $e->nb_salles = $usage[$e->direction_id][$e->nom] ?? 0;
        }

        $directions = $this->directionsAccessibles();

        return view('equipements.index', compact('equipements', 'directions'));
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
            'noms.required' => 'Saisissez au moins un équipement.',
        ]);

        $noms = collect(preg_split('/[,;\n]+/', $validated['noms']))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique(fn ($n) => mb_strtolower($n))
            ->values();

        if ($noms->isEmpty()) {
            return back()->withErrors(['noms' => 'Saisissez au moins un équipement.'])->withInput();
        }

        if ($noms->contains(fn ($n) => mb_strlen($n) > 100)) {
            return back()->withErrors(['noms' => "Un nom d'équipement ne doit pas dépasser 100 caractères."])->withInput();
        }

        $existants = Equipement::where('direction_id', $validated['direction_id'])
            ->pluck('nom')
            ->map(fn ($n) => mb_strtolower($n))
            ->all();

        $nouveaux = $noms->reject(fn ($n) => in_array(mb_strtolower($n), $existants, true));

        if ($nouveaux->isEmpty()) {
            return back()->withErrors(['noms' => 'Cet équipement existe déjà dans cette direction.'])->withInput();
        }

        foreach ($nouveaux as $nom) {
            Equipement::create([
                'direction_id' => $validated['direction_id'],
                'nom' => $nom,
            ]);
        }

        return redirect()->route('admin.equipements.index')
            ->with('success', $nouveaux->count() > 1 ? 'Équipements ajoutés avec succès.' : 'Équipement ajouté avec succès.');
    }

    public function update(Request $request, Equipement $equipement)
    {
        $this->verifierAffectation();
        $this->autoriser($equipement);

        $validated = $request->validate([
            'nom' => [
                'required', 'string', 'max:100', 'not_regex:/[,;]/',
                Rule::unique('equipements', 'nom')
                    ->where('direction_id', $equipement->direction_id)
                    ->ignore($equipement->id),
            ],
        ], [
            'nom.required' => "Le nom de l'équipement est obligatoire.",
            'nom.unique' => 'Cet équipement existe déjà dans cette direction.',
            'nom.not_regex' => "Le nom ne doit pas contenir de virgule ni de point-virgule.",
        ]);

        $ancien = $equipement->nom;
        $nouveau = trim($validated['nom']);

        // Les salles qui utilisent cet équipement suivent le nouveau nom
        if ($ancien !== $nouveau) {
            foreach ($this->sallesUtilisant($equipement) as $salle) {
                $salle->equipement = implode(', ', array_map(
                    fn ($e) => $e === $ancien ? $nouveau : $e,
                    $this->liste($salle->equipement)
                ));
                $salle->save();
            }
        }

        $equipement->update(['nom' => $nouveau]);

        return redirect()->route('admin.equipements.index')
            ->with('success', 'Équipement modifié avec succès.');
    }

    public function destroy(Equipement $equipement)
    {
        $this->verifierAffectation();
        $this->autoriser($equipement);

        $nom = $equipement->nom;

        // L'équipement est retiré automatiquement de toutes les salles qui l'utilisent
        foreach ($this->sallesUtilisant($equipement) as $salle) {
            $reste = array_values(array_filter(
                $this->liste($salle->equipement),
                fn ($e) => $e !== $nom
            ));

            $salle->equipement = $reste ? implode(', ', $reste) : null;
            $salle->save();
        }

        $equipement->delete();

        return redirect()->route('admin.equipements.index')
            ->with('success', 'Équipement supprimé avec succès.');
    }
}