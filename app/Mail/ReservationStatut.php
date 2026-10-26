<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationStatut extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Nombre d'essais en cas d'échec d'envoi, avec 60 secondes d'attente entre chaque. */
    public int $tries = 3;
    public int $backoff = 60;

    /**
     * @param string $type 'confirmation' ou 'refus'
     */
    public function __construct(
        public Reservation $reservation,
        public string $texte,
        public string $type
    ) {
    }

    public function envelope(): Envelope
    {
        $sujet = $this->type === 'confirmation'
            ? 'Votre réservation de salle est confirmée'
            : 'Votre réservation de salle a été refusée';

        return new Envelope(subject: $sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reservation-statut');
    }
}