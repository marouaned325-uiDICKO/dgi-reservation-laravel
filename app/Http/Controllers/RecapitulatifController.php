<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Reservation;
use App\Models\Salle;
use App\Models\Service;
use App\Models\User;

class RecapitulatifController extends Controller
{
    /** Récapitulatif de chaque direction (réservé aux administrateurs). */
    public function index()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $directions = Direction::orderBy('nom_direction')->get();

        $services = Service::whereNotNull('direction_id')
            ->selectRaw('direction_id, COUNT(*) as total')
            ->groupBy('direction_id')->pluck('total', 'direction_id');

        // Salles : total et par statut
        $salles = Salle::whereNotNull('direction_id')
            ->selectRaw('direction_id, statut, COUNT(*) as total')
            ->groupBy('direction_id', 'statut')->get()
            ->groupBy('direction_id')
            ->map(fn ($lignes) => $lignes->pluck('total', 'statut'));

        // Utilisateurs par rôle (hors administrateurs)
        $users = User::whereNotNull('direction_id')->where('role', '!=', 'admin')
            ->selectRaw('direction_id, role, COUNT(*) as total')
            ->groupBy('direction_id', 'role')->get()
            ->groupBy('direction_id')
            ->map(fn ($lignes) => $lignes->pluck('total', 'role'));

        // Réservations des salles de chaque direction, par statut
        $reservations = Reservation::join('salles', 'salles.id', '=', 'reservations.salle_id')
            ->whereNotNull('salles.direction_id')
            ->selectRaw('salles.direction_id as direction_id, reservations.statut as statut, COUNT(*) as total')
            ->groupBy('salles.direction_id', 'reservations.statut')->get()
            ->groupBy('direction_id')
            ->map(fn ($lignes) => $lignes->pluck('total', 'statut'));

        $lignes = $directions->map(function ($d) use ($services, $salles, $users, $reservations) {
            $s = $salles[$d->id] ?? collect();
            $u = $users[$d->id] ?? collect();
            $r = $reservations[$d->id] ?? collect();

            $nbSalles = (int) $s->sum();

            return [
                'id' => $d->id,
                'nom' => $d->nom_direction,
                'services' => (int) ($services[$d->id] ?? 0),
                'salles' => $nbSalles,
                'salles_dispo' => (int) ($s['disponible'] ?? 0),
                'salles_hors' => (int) ($s['indisponible'] ?? 0) + (int) ($s['maintenance'] ?? 0),
                'agents' => (int) ($u['agent'] ?? 0),
                'gestionnaires' => (int) ($u['gestionnaire'] ?? 0),
                'admins' => 0, // colonne conservée pour la vue ; les administrateurs de direction n'existent plus
                'res_total' => (int) $r->sum(),
                'res_attente' => (int) ($r['en_attente'] ?? 0),
                'res_confirmee' => (int) ($r['confirmee'] ?? 0),
                'res_refusee' => (int) ($r['refusee'] ?? 0),
                'sans_gestionnaire' => $nbSalles > 0 && (int) ($u['gestionnaire'] ?? 0) === 0,
            ];
        });

        $totaux = [
            'services' => $lignes->sum('services'),
            'salles' => $lignes->sum('salles'),
            'salles_dispo' => $lignes->sum('salles_dispo'),
            'salles_hors' => $lignes->sum('salles_hors'),
            'agents' => $lignes->sum('agents'),
            'gestionnaires' => $lignes->sum('gestionnaires'),
            'admins' => $lignes->sum('admins'),
            'res_total' => $lignes->sum('res_total'),
            'res_attente' => $lignes->sum('res_attente'),
            'res_confirmee' => $lignes->sum('res_confirmee'),
            'res_refusee' => $lignes->sum('res_refusee'),
        ];

        $sallesSansDirection = Salle::whereNull('direction_id')->count();

        return view('recapitulatif', compact('lignes', 'totaux', 'sallesSansDirection'));
    }
}