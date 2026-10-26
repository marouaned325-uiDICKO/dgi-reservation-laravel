<?php

namespace App\Http\Controllers;

use App\Models\Motif;
use App\Models\Reservation;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        $salleId = $request->query('salle');
        $salle = $salleId ? Salle::find($salleId) : Salle::first();

        if (!$salle) {
            return redirect()->route('salles.liste')
                ->withErrors(['salle' => 'Salle introuvable.']);
        }

        if ($salle->statut !== 'disponible') {
            return redirect()->route('salles.liste')
                ->withErrors(['salle' => 'Cette salle n\'est actuellement pas disponible (statut : ' . $salle->statut . '). Réservation impossible pour le moment.']);
        }

        // Motifs propres à la direction de la salle (liste par défaut s'il n'y en a pas)
        $motifs = Motif::where('direction_id', $salle->direction_id)->orderBy('nom')->pluck('nom')->all();

        if (empty($motifs)) {
            $motifs = ['Réunion', 'Conférence', 'Formation', 'Atelier', 'Séminaire', 'Entretien', 'Présentation', 'Autre'];
        }

        return view('reservations.create', [
            'salle'   => $salle,
            'salleId' => $salle->id,
            'motifs'  => $motifs,
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'motif' => 'required|string',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => [
                'nullable',
                'date_format:H:i',
                Rule::when($request->filled('heure_debut'), ['after:heure_debut']),
            ],
            'nombre' => 'required|integer|min:1',
            'salle_id' => 'required|exists:salles,id',
        ], [
            'date_debut.after_or_equal' => 'Impossible de réserver une date passée.',
            'date_fin.after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',
            'heure_fin.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'nombre.min' => 'Le nombre de participants doit être au moins de 1.',
        ]);

        $user = $request->user();
        $salle = Salle::find($validated['salle_id']);

        if ($salle->statut !== 'disponible') {
            return back()->withErrors(['nombre' => 'Cette salle n\'est actuellement pas disponible (statut : ' . $salle->statut . ').'])->withInput();
        }

        if ($validated['nombre'] > $salle->capacite) {
            return back()
                ->withErrors(['nombre' => 'Le nombre de participants dépasse la capacité de la salle (' . $salle->capacite . ' places).'])
                ->withInput();
        }

        $conflit = $salle->reservations()
            ->where('statut', 'confirmee')
            ->chevauche(
                $validated['date_debut'],
                $validated['date_fin'],
                $validated['heure_debut'] ?? null,
                $validated['heure_fin'] ?? null
            )
            ->exists();

        if ($conflit) {
            return back()->withErrors(['heure_debut' => 'La salle est déjà réservée sur ce créneau.'])->withInput();
        }

        Reservation::create([
            'user_id' => $user->id,
            'reference_utilisateur' => $user->reference,
            'salle_id' => $salle->id,
            'direction' => $user->direction->nom_direction ?? null,
            'service' => $user->service->nom_service ?? null,
            'motif' => $validated['motif'],
            'email' => $user->email,
            'date_reserv' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'heure_debut' => $validated['heure_debut'] ?? null,
            'heure_fin' => $validated['heure_fin'] ?? null,
            'nombre' => $validated['nombre'],
            'statut' => 'en_attente',
        ]);

        return redirect()->route('reservations.mes_demandes')
            ->with('success', 'Votre demande de réservation a bien été envoyée. Vous recevrez une notification dès qu\'elle sera traitée.');
    }

    public function succes()
    {
        return view('reservations.succes');
    }

    public function mesDemandes(Request $request)
    {
        $statut = $request->query('statut', 'toutes');

        $query = Reservation::where('user_id', $request->user()->id)
            ->with(['salle', 'traitePar'])
            ->orderByDesc('created_at');

        if ($statut !== 'toutes') {
            $query->where('statut', $statut);
        }

        $demandes = $query->get();

        return view('reservations.mes_demandes', compact('demandes', 'statut'));
    }

    public function annuler(Request $request, Reservation $reservation)
    {
        if ((int) $reservation->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($reservation->statut !== 'en_attente') {
            return redirect()->route('reservations.mes_demandes')
                ->withErrors(['statut' => 'Seules les demandes en attente peuvent être annulées.']);
        }

        $reservation->statut = 'annulee';
        $reservation->save();

        return redirect()->route('reservations.mes_demandes')
            ->with('success', 'Votre demande a bien été annulée.');
    }

    public function edit(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($reservation->statut !== 'en_attente') {
            return redirect()->route('reservations.mes_demandes')
                ->withErrors(['statut' => 'Seules les demandes en attente peuvent être modifiées.']);
        }

        $reservation->load('salle');

        // Motifs propres à la direction de la salle (la vue utilise une liste par défaut si vide)
        $motifs = Motif::where('direction_id', $reservation->salle->direction_id)->orderBy('nom')->pluck('nom')->all();

        return view('reservations.edit', compact('reservation', 'motifs'));
    }
    public function update(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($reservation->statut !== 'en_attente') {
            abort(403, 'Cette demande ne peut plus être modifiée.');
        }

        $validated = $request->validate([
            'motif' => 'required|string',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => [
                'nullable',
                'date_format:H:i',
                Rule::when($request->filled('heure_debut'), ['after:heure_debut']),
            ],
            'nombre' => 'required|integer|min:1',
        ], [
            'date_debut.after_or_equal' => 'Impossible de réserver une date passée.',
            'date_fin.after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',
            'heure_fin.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'nombre.min' => 'Le nombre de participants doit être au moins de 1.',
        ]);

        $salle = $reservation->salle;

        if ($salle->statut !== 'disponible') {
            return back()->withErrors(['nombre' => 'Cette salle n\'est actuellement pas disponible (statut : ' . $salle->statut . ').'])->withInput();
        }

        if ($validated['nombre'] > $salle->capacite) {
            return back()
                ->withErrors(['nombre' => 'Le nombre de participants dépasse la capacité de la salle (' . $salle->capacite . ' places).'])
                ->withInput();
        }

        $conflit = $salle->reservations()
            ->where('id', '!=', $reservation->id)
            ->where('statut', 'confirmee')
            ->chevauche(
                $validated['date_debut'],
                $validated['date_fin'],
                $validated['heure_debut'] ?? null,
                $validated['heure_fin'] ?? null
            )
            ->exists();

        if ($conflit) {
            return back()->withErrors(['heure_debut' => 'La salle est déjà réservée sur ce créneau.'])->withInput();
        }

        $reservation->update([
            'motif' => $validated['motif'],
            'email' => $reservation->utilisateur->email,
            'date_reserv' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'heure_debut' => $validated['heure_debut'] ?? null,
            'heure_fin' => $validated['heure_fin'] ?? null,
            'nombre' => $validated['nombre'],
        ]);

        return redirect()->route('reservations.mes_demandes')->with('success', 'Demande modifiée avec succès.');
    }

    public function historique(Request $request)
    {
        $user = $request->user();

        // Gestionnaire : demandes des salles de sa direction ; admin : toutes les demandes
        if (in_array($user->role, ['gestionnaire', 'admin'], true)) {
            $query = Reservation::with(['salle', 'utilisateur'])->orderByDesc('created_at');

            if ($user->role !== 'admin') {
                abort_unless($user->direction_id, 403);
                $query->whereHas('salle', fn ($s) => $s->where('direction_id', $user->direction_id));
            }

            $demandes = $query->get();

            return view('historique.index', compact('demandes'));
        }

        // Agent : son propre historique
        $demandes = Reservation::where('user_id', $user->id)
            ->with('salle')
            ->orderByDesc('created_at')
            ->get();

        return view('historique.agent', compact('demandes'));
    }
}