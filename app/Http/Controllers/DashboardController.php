<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Reservation;
use App\Models\Salle;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->expirerReservations();

        $user = $request->user();

        // Administrateur (simple ou système) : chiffres de toute la plateforme
        if ($user->role === 'admin') {
            return $this->dashboardAdmin();
        }

        // Gestionnaire : demandes des salles de sa direction
        if ($user->role === 'gestionnaire') {
            return $this->dashboardGestionnaire($user->direction_id ?? 0);
        }

        return $this->dashboardAgent($user);
    }

    /**
     * Bascule en "refusee" toute demande "en_attente" dont le délai
     * (date_fin, ou date_reserv si pas de date_fin) est dépassé.
     * Appelé à chaque chargement du dashboard / de la page de validation,
     * pour que le statut en base reste toujours à jour.
     */
    private function expirerReservations()
    {
        Reservation::where('statut', 'en_attente')
            ->where(function ($q) {
                $q->whereDate('date_fin', '<', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('date_fin')->whereDate('date_reserv', '<', now());
                  });
            })
            ->update([
                'statut' => 'refusee',
                'motif_refus' => 'Délai dépassé sans traitement',
            ]);
    }

    /** Réservations visibles : toutes (direction = null) ou celles des salles d'une direction. */
    private function reservationsDe(?int $directionId)
    {
        $query = Reservation::query();

        if ($directionId !== null) {
            $query->whereHas('salle', fn ($s) => $s->where('direction_id', $directionId));
        }

        return $query;
    }

    private function sallesDe(?int $directionId)
    {
        $query = Salle::query();

        if ($directionId !== null) {
            $query->where('direction_id', $directionId);
        }

        return $query;
    }

    /** Tableau de bord de l'administrateur : toute la plateforme. */
    private function dashboardAdmin()
    {
        $aujourdhui = now()->toDateString();

        $statsUtilisateurs = [
            'agents' => User::where('role', 'agent')->count(),
            'gestionnaires' => User::where('role', 'gestionnaire')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'total' => User::count(),
        ];

        $statsSalles = [
            'total' => Salle::count(),
            'disponibles' => Salle::where('statut', 'disponible')->count(),
            'indisponibles' => Salle::where('statut', 'indisponible')->count(),
            'maintenance' => Salle::where('statut', 'maintenance')->count(),
            'sans_direction' => Salle::whereNull('direction_id')->count(),
        ];

        $statsReservations = [
            'total' => Reservation::count(),
            'en_attente' => Reservation::where('statut', 'en_attente')->count(),
            'confirmee' => Reservation::where('statut', 'confirmee')->count(),
            'refusee' => Reservation::where('statut', 'refusee')->count(),
        ];

        $sallesOccupeesAujourdhui = Reservation::where('statut', 'confirmee')
            ->whereDate('date_reserv', '<=', $aujourdhui)
            ->whereRaw('COALESCE(date_fin, date_reserv) >= ?', [$aujourdhui])
            ->distinct('salle_id')
            ->count('salle_id');

        $statsStructure = [
            'directions' => Direction::count(),
            'services' => Service::count(),
        ];

        // Récapitulatif par direction
        $sallesParDirection = Salle::whereNotNull('direction_id')
            ->selectRaw('direction_id, COUNT(*) as total')
            ->groupBy('direction_id')->pluck('total', 'direction_id');

        $usersParDirection = User::whereNotNull('direction_id')->where('role', '!=', 'admin')
            ->selectRaw('direction_id, COUNT(*) as total')
            ->groupBy('direction_id')->pluck('total', 'direction_id');

        $gestionnairesParDirection = User::where('role', 'gestionnaire')->whereNotNull('direction_id')
            ->selectRaw('direction_id, COUNT(*) as total')
            ->groupBy('direction_id')->pluck('total', 'direction_id');

        $attenteParDirection = Reservation::join('salles', 'salles.id', '=', 'reservations.salle_id')
            ->where('reservations.statut', 'en_attente')
            ->whereNotNull('salles.direction_id')
            ->selectRaw('salles.direction_id as direction_id, COUNT(*) as total')
            ->groupBy('salles.direction_id')->pluck('total', 'direction_id');

        $directions = Direction::orderBy('nom_direction')->get();

        $directionsSansGestionnaire = $directions->filter(
            fn ($d) => ($sallesParDirection[$d->id] ?? 0) > 0 && ($gestionnairesParDirection[$d->id] ?? 0) === 0
        )->count();

        $derniersUtilisateurs = User::with('direction', 'service')->latest()->take(5)->get();

        return view('dashboard_admin', compact(
            'statsUtilisateurs', 'statsSalles', 'statsStructure', 'statsReservations',
            'sallesOccupeesAujourdhui', 'directions', 'sallesParDirection', 'usersParDirection',
            'gestionnairesParDirection', 'attenteParDirection', 'directionsSansGestionnaire',
            'derniersUtilisateurs'
        ));
    }

    private function dashboardGestionnaire(int $directionId)
    {
        $stats = [
            'en_cours' => $this->reservationsDe($directionId)->where('statut', 'en_attente')->count(),
            'validees' => $this->reservationsDe($directionId)->where('statut', 'confirmee')->count(),
            'rejetees' => $this->reservationsDe($directionId)->where('statut', 'refusee')->count(),
            'total_salles' => $this->sallesDe($directionId)->count(),
        ];

        $statsMotif = [
            'conference' => $this->reservationsDe($directionId)->where('motif', 'CONFERENCE')->count(),
            'reunion' => $this->reservationsDe($directionId)->where('motif', 'REUNION')->count(),
        ];

        $statsSalles = [
            'disponibles' => $this->sallesDe($directionId)->where('statut', 'disponible')->count(),
            'indisponibles' => $this->sallesDe($directionId)->where('statut', 'indisponible')->count(),
            'maintenance' => $this->sallesDe($directionId)->where('statut', 'maintenance')->count(),
        ];

        $reservationsAujourdhui = $this->reservationsDe($directionId)->where('statut', 'confirmee')
            ->whereDate('date_reserv', '<=', now())
            ->whereDate('date_fin', '>=', now())
            ->with(['salle', 'utilisateur'])
            ->get();

        $demandesEnAttente = $this->reservationsDe($directionId)->where('statut', 'en_attente')
            ->with(['salle', 'utilisateur'])
            ->orderBy('created_at')
            ->take(5)
            ->get();

        return view('dashboard_gestionnaire', compact(
            'stats', 'statsMotif', 'statsSalles', 'reservationsAujourdhui', 'demandesEnAttente'
        ));
    }

    private function dashboardAgent($user)
    {
        $mesReservations = Reservation::where('user_id', $user->id);

        $stats = [
            'en_cours' => (clone $mesReservations)->where('statut', 'en_attente')->count(),
            'validees' => (clone $mesReservations)->where('statut', 'confirmee')->count(),
            'rejetees' => (clone $mesReservations)->where('statut', 'refusee')->count(),
        ];

        // Les 3 demandes les plus récentes, la plus récente en premier
        $dernieresDemandes = (clone $mesReservations)
            ->with('salle')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        return view('dashboard_agent', compact('stats', 'dernieresDemandes'));
    }
}