<?php

namespace App\Support;

use Illuminate\Support\Str;

class Notif
{
    /** Messages d'ajout ou de modification : affichés en bandeau vert en haut de la page. */
    public static function estBandeau(?string $message): bool
    {
        if (! $message) {
            return false;
        }

        return Str::contains(Str::lower($message), [
            'ajouté', 'créé', 'enregistré', 'modifié', 'mis à jour', 'mise à jour',
        ]);
    }
}