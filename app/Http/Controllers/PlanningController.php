<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Salle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Les agents et les administrateurs voient toutes les salles.
        // Les gestionnaires : uniquement celles de leur direction.
        $query = Salle::orderBy('nom');

        if ($user->role === 'gestionnaire') {
            $query->where('direction_id', $user->direction_id ?? 0);
        }

        $salles = $query->get();

        $salleId = (int) $request->query('salle', $salles->first()?->id);

        // Une salle hors de la liste autorisée est ignorée
        if (! $salles->contains('id', $salleId)) {
            $salleId = (int) $salles->first()?->id;
        }

        $jours = collect(range(0, 6))->map(fn ($i) => Carbon::today()->addDays($i));

        $reservationsParJour = [];
        if ($salleId) {
            foreach ($jours as $jour) {
                $reservationsParJour[$jour->format('Y-m-d')] = Reservation::where('salle_id', $salleId)
                    ->where('date_reserv', $jour->format('Y-m-d'))
                    ->whereIn('statut', ['en_attente', 'confirmee'])
                    ->orderBy('heure_debut')
                    ->get();
            }
        }

        return view('planning.index', [
            'salles' => $salles,
            'salleId' => $salleId,
            'jours' => $jours,
            'reservationsParJour' => $reservationsParJour,
        ]);
    }
}