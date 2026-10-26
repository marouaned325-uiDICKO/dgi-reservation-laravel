<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationStatut extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $texte,
        public string $type // 'confirmation' ou 'refus'
    ) {}

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