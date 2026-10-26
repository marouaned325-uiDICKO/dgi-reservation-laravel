<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    /**
     * Affiche le formulaire de changement de mot de passe obligatoire.
     */
    public function show()
    {
        // Mot de passe déjà changé : pas besoin de rester sur cette page
        if (! Auth::user()->doit_changer_mot_de_passe) {
            return redirect()->route('dashboard');
        }

        return view('auth.changer_mot_de_passe');
    }

    /**
     * Enregistre le nouveau mot de passe (libre, 8 caractères minimum).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                function ($attribute, $value, $fail) use ($user) {
                    if (Hash::check($value, $user->password)) {
                        $fail('Le nouveau mot de passe doit être différent de l\'ancien.');
                    }
                },
            ],
        ], [
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user->password = Hash::make($request->password);
        $user->doit_changer_mot_de_passe = false;
        $user->save();

        // Nouvelle session par sécurité
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Votre mot de passe a été modifié avec succès.');
    }
}