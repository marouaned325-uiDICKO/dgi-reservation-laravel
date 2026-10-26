<?php

namespace App\Http\Controllers;

use App\Mail\ReservationStatut;
use App\Models\Notification;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ValidationController extends Controller
{
    private const LIBELLES_STATUT = [
        'en_attente' => 'En attente',
        'confirmee'  => 'Confirmée',
        'refusee'    => 'Refusée',
        'annulee'    => 'Annulée',
    ];

    /** Un gestionnaire ne traite que les demandes des salles de sa direction. */
    private function verifierAffectation(): void
    {
        $u = auth()->user();
        abort_if($u->role !== 'admin' && ! $u->direction_id, 403, "Votre compte n'est rattaché à aucune direction.");
    }

    private function autoriser(Reservation $reservation): void
    {
        $u = auth()->user();

        abort_unless(
            $u->role === 'admin' || (int) $reservation->salle->direction_id === (int) $u->direction_id,
            403,
            "Cette demande concerne une salle d'une autre direction."
        );
    }

    /**
     * Une demande en attente dont la date est dépassée est considérée comme refusée.
     */
    private function estExpiree(Reservation $r): bool
    {
        if ($r->statut !== 'en_attente') {
            return false;
        }

        return Carbon::parse($r->date_fin ?? $r->date_reserv)->endOfDay()->isPast();
    }

    private function statutEffectif(Reservation $r): string
    {
        return $this->estExpiree($r) ? 'refusee' : $r->statut;
    }

    /**
     * Crée la notification dans l'application ET envoie l'email au demandeur.
     * L'email part en arrière-plan (file d'attente) si le Mailable implémente ShouldQueue.
     * Si l'envoi échoue, la validation n'est pas bloquée : l'erreur est juste enregistrée dans le log.
     */
    private function notifier(Reservation $reservation, string $message, string $type): void
    {
        Notification::create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'message' => $message,
            'type' => $type,
        ]);

        $email = $reservation->utilisateur?->email;

        if (! $email) {
            Log::warning("Réservation {$reservation->id} : aucun email pour l'utilisateur {$reservation->user_id}.");
            return;
        }

        try {
            Mail::to($email)->send(new ReservationStatut($reservation, $message, $type));
        } catch (\Throwable $e) {
            Log::error("Échec de l'envoi de l'email (réservation {$reservation->id}) : " . $e->getMessage());
        }
    }

    /**
     * Demandes visibles par l'utilisateur, filtrées par statut (et par recherche pour l'export).
     * Une demande expirée compte comme "refusée", jamais comme "en attente".
     */
    private function demandesFiltrees(Request $request): array
    {
        $this->verifierAffectation();
        $u = auth()->user();
        $statut = $request->query('statut', 'toutes');
        $aujourdhui = now()->toDateString();

        $query = Reservation::with(['salle', 'utilisateur', 'traitePar'])->orderByDesc('created_at');

        if ($u->role !== 'admin') {
            $query->whereHas('salle', fn ($s) => $s->where('direction_id', $u->direction_id));
        }

        if ($statut === 'en_attente') {
            // En attente = pas encore traitée ET date non dépassée
            $query->where('statut', 'en_attente')
                ->whereRaw('COALESCE(date_fin, date_reserv) >= ?', [$aujourdhui]);
        } elseif ($statut === 'refusee') {
            // Refusée = refusée, ou en attente mais expirée
            $query->where(function ($q) use ($aujourdhui) {
                $q->where('statut', 'refusee')
                  ->orWhere(function ($q2) use ($aujourdhui) {
                      $q2->where('statut', 'en_attente')
                         ->whereRaw('COALESCE(date_fin, date_reserv) < ?', [$aujourdhui]);
                  });
            });
        } elseif ($statut !== 'toutes') {
            $query->where('statut', $statut);
        }

        return [$query->get(), $statut];
    }

    public function index(Request $request)
    {
        [$demandes, $statut] = $this->demandesFiltrees($request);

        return view('validation.index', compact('demandes', 'statut'));
    }

    /**
     * Export Excel de la liste affichée (même filtre de statut + même recherche).
     */
    public function exporter(Request $request)
    {
        [$demandes, $statut] = $this->demandesFiltrees($request);

        $lignes = $demandes->map(function (Reservation $r) {
            $debut = Carbon::parse($r->date_reserv);
            $fin = $r->date_fin ? Carbon::parse($r->date_fin) : null;
            $periode = ($fin && ! $fin->isSameDay($debut))
                ? $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y')
                : $debut->format('d/m/Y');

            $hd = $r->heure_debut ? substr($r->heure_debut, 0, 5) : null;
            $hf = $r->heure_fin ? substr($r->heure_fin, 0, 5) : null;
            $horaire = ($hd && $hf) ? $hd . ' - ' . $hf
                : ($hd ? 'À partir de ' . $hd
                : ($hf ? "Jusqu'à " . $hf : 'Journée entière'));

            $direction = is_object($r->direction) ? ($r->direction->nom_direction ?? '-') : ($r->direction ?? '-');
            $service = is_object($r->service) ? ($r->service->nom_service ?? '-') : ($r->service ?? '-');

            $statutAffiche = $this->statutEffectif($r);
            $traitee = in_array($r->statut, ['confirmee', 'refusee'], true);

            $motifRefus = $this->estExpiree($r)
                ? 'Délai dépassé sans traitement'
                : ($r->statut === 'refusee' ? (string) $r->motif_refus : '');

            return [
                (string) $r->reference,
                $r->utilisateur->name ?? 'Utilisateur supprimé',
                $direction . ' / ' . $service,
                ($r->salle->nom ?? 'Salle supprimée') . ' (' . ($r->salle->localite ?? '-') . ')',
                (string) $r->motif,
                $periode,
                $horaire,
                (int) ($r->nombre ?? 0),
                self::LIBELLES_STATUT[$statutAffiche] ?? ucfirst(str_replace('_', ' ', $statutAffiche)),
                $traitee ? ($r->traitePar->name ?? '') : '',
                ($traitee && $r->traite_le) ? Carbon::parse($r->traite_le)->format('d/m/Y H:i') : '',
                $motifRefus,
                $r->created_at->format('d/m/Y H:i'),
            ];
        });

        // Même recherche que celle saisie dans le tableau
        $recherche = mb_strtolower(trim((string) $request->query('recherche', '')));
        if ($recherche !== '') {
            $lignes = $lignes->filter(
                fn ($l) => str_contains(mb_strtolower(implode(' ', array_slice($l, 0, 9))), $recherche)
            );
        }

        $entetes = [
            'Référence', 'Agent', 'Direction / Service', 'Salle', 'Motif', 'Période', 'Horaire',
            'Participants', 'Statut', 'Traitée par', 'Date de traitement', 'Motif du refus', 'Demande déposée le',
        ];

        $spreadsheet = new Spreadsheet();
        $feuille = $spreadsheet->getActiveSheet();
        $feuille->setTitle('Demandes');

        $feuille->fromArray($entetes, null, 'A1');
        $feuille->fromArray($lignes->values()->all(), null, 'A2');

        $derniereColonne = Coordinate::stringFromColumnIndex(count($entetes));
        $style = $feuille->getStyle("A1:{$derniereColonne}1");
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1A8969');
        $feuille->freezePane('A2');

        for ($i = 1; $i <= count($entetes); $i++) {
            $feuille->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $suffixe = $statut === 'toutes' ? 'toutes' : $statut;
        $nomFichier = 'demandes_reservation_' . $suffixe . '_' . now()->format('Y-m-d_Hi') . '.xlsx';

        $ecrivain = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($ecrivain) {
            $ecrivain->save('php://output');
        }, $nomFichier, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function traiter(Request $request, Reservation $reservation)
    {
        $this->verifierAffectation();
        $this->autoriser($reservation);
        $u = auth()->user();

        if ($reservation->statut !== 'en_attente') {
            return redirect()->route('validation.index')
                ->withErrors(['conflit' => 'Cette demande a déjà été traitée.']);
        }

        if ($this->estExpiree($reservation)) {
            return redirect()->route('validation.index')
                ->withErrors(['conflit' => 'Cette demande est expirée : sa date est dépassée.']);
        }

        $action = $request->input('action');

        if ($action === 'valider') {
            $conflit = $reservation->salle->reservations()
                ->where('id', '!=', $reservation->id)
                ->where('statut', 'confirmee')
                ->chevauche(
                    Carbon::parse($reservation->date_reserv)->toDateString(),
                    Carbon::parse($reservation->date_fin ?? $reservation->date_reserv)->toDateString(),
                    $reservation->heure_debut,
                    $reservation->heure_fin
                )
                ->exists();

            if ($conflit) {
                return redirect()->route('validation.index')
                    ->withErrors(['conflit' => 'Une autre réservation est déjà confirmée sur ce créneau pour cette salle.']);
            }

            $reservation->statut = 'confirmee';
            $reservation->traite_par = $u->id;
            $reservation->traite_le = now();
            $reservation->save();

            $this->notifier(
                $reservation,
                "Votre réservation de {$reservation->salle->nom} le {$reservation->date_reserv->format('d/m/Y')} a été confirmée.",
                'confirmation'
            );

            $demandesConcurrentes = $reservation->salle->reservations()
                ->where('id', '!=', $reservation->id)
                ->where('statut', 'en_attente')
                ->chevauche(
                    Carbon::parse($reservation->date_reserv)->toDateString(),
                    Carbon::parse($reservation->date_fin ?? $reservation->date_reserv)->toDateString(),
                    $reservation->heure_debut,
                    $reservation->heure_fin
                )
                ->get();

            foreach ($demandesConcurrentes as $demande) {
                $demande->statut = 'refusee';
                $demande->motif_refus = 'Salle indisponible : une autre réservation a été confirmée sur ce créneau.';
                $demande->traite_le = now();
                $demande->save();

                $this->notifier(
                    $demande,
                    "Votre réservation de {$demande->salle->nom} le {$demande->date_reserv->format('d/m/Y')} a été refusée. Motif : Salle indisponible sur ce créneau.",
                    'refus'
                );
            }

            return redirect()->route('validation.index');

        } elseif ($action === 'refuser') {
            $request->validate([
                'motif_refus' => 'required|string|max:500',
            ], [
                'motif_refus.required' => 'Veuillez indiquer un motif de refus.',
            ]);

            $reservation->statut = 'refusee';
            $reservation->motif_refus = $request->input('motif_refus');
            $reservation->traite_par = $u->id;
            $reservation->traite_le = now();
            $reservation->save();

            $this->notifier(
                $reservation,
                "Votre réservation de {$reservation->salle->nom} le {$reservation->date_reserv->format('d/m/Y')} a été refusée. Motif : " . $request->input('motif_refus'),
                'refus'
            );

            return redirect()->route('validation.index');
        }

        return redirect()->route('validation.index');
    }
}