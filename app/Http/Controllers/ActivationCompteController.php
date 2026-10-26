<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivationCompteController extends Controller
{
    /**
     * Lien de connexion reçu par courriel à la création du compte.
     * Il connecte l'utilisateur puis l'envoie sur la page « Changer le mot de passe ».
     */
    public function connecter(Request $request, User $user)
    {
        // Mot de passe déjà choisi : le lien ne sert plus
        if (! $user->doit_changer_mot_de_passe) {
            return redirect()->route('login')
                ->with('success', 'Votre mot de passe est déjà défini. Connectez-vous.');
        }

        if ($user->compteExpire()) {
            return redirect()->route('login')
                ->withErrors(['email' => "Ce lien a expiré. Demandez à un administrateur de réactiver votre compte."]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('mot-de-passe.changer');
            }
}