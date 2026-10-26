<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class AttestationController extends Controller
{
    /**
     * Attestation PDF de validation d'une réservation de salle.
     * Accessible : au demandeur, au gestionnaire de la direction de la salle, et à l'administrateur.
     */
    public function reservation(Request $request, Reservation $reservation)
    {
        $u = $request->user();
        $reservation->load(['salle.direction', 'utilisateur', 'traitePar']);

        $autorise = $u->role === 'admin'
            || (int) $reservation->user_id === (int) $u->id
            || (
                $u->role === 'gestionnaire'
                && $u->direction_id
                && (int) optional($reservation->salle)->direction_id === (int) $u->direction_id
            );

        abort_unless($autorise, 403, "Vous n'êtes pas autorisé à consulter cette attestation.");
        abort_unless($reservation->statut === 'confirmee', 403, "L'attestation n'est disponible que pour une réservation confirmée.");

        $debut = Carbon::parse($reservation->date_reserv);
        $fin = $reservation->date_fin ? Carbon::parse($reservation->date_fin) : $debut;
        $periode = $debut->isSameDay($fin)
            ? $debut->format('d/m/Y')
            : $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');

        $hd = $reservation->heure_debut ? substr($reservation->heure_debut, 0, 5) : null;
        $hf = $reservation->heure_fin ? substr($reservation->heure_fin, 0, 5) : null;
        $horaire = ($hd && $hf) ? $hd . ' - ' . $hf
            : ($hd ? 'À partir de ' . $hd
            : ($hf ? "Jusqu'à " . $hf : 'Journée entière'));

        $direction = is_object($reservation->direction)
            ? ($reservation->direction->nom_direction ?? '-')
            : ($reservation->direction ?: '-');
        $service = is_object($reservation->service)
            ? ($reservation->service->nom_service ?? '-')
            : ($reservation->service ?: '-');

        $valideur = $reservation->traitePar;
        $roleValideur = $valideur
            ? ($valideur->role === 'admin'
                ? ($valideur->libelle_role ?? 'Administrateur')
                : ($valideur->role === 'gestionnaire' ? 'Gestionnaire' : ucfirst((string) $valideur->role)))
            : '';

        $logo = public_path('images/logo-dgi.png');

        $html = view('attestations.reservation', [
            'r'            => $reservation,
            'salle'        => $reservation->salle,
            'demandeur'    => $reservation->utilisateur,
            'direction'    => $direction,
            'service'      => $service,
            'periode'      => $periode,
            'horaire'      => $horaire,
            'valideur'     => $valideur,
            'roleValideur' => $roleValideur,
            'dateValidation' => $reservation->traite_le
                ? Carbon::parse($reservation->traite_le)->format('d/m/Y à H:i')
                : '-',
            'logo'         => file_exists($logo) ? $logo : null,
            'genereLe'     => now()->format('d/m/Y à H:i'),
        ])->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->setChroot(base_path());

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $nom = 'Attestation_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $reservation->reference) . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($request->boolean('telecharger') ? 'attachment' : 'inline') . '; filename="' . $nom . '"',
        ]);
    }
}