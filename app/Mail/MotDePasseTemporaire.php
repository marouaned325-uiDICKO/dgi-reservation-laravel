<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MotDePasseTemporaire extends Mailable
{
    use SerializesModels;

    public function __construct(public User $user, public string $motDePasse)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre mot de passe temporaire - DGI Gestion des salles');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.mot-de-passe-temporaire');
    }
}