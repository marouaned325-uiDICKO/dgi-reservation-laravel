<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompteCree extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Nombre d'essais en cas d'échec d'envoi, avec 60 secondes d'attente entre chaque. */
    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public User $user, public string $lien)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre compte - Gestion des salles DGI');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.compte-cree');
    }
}