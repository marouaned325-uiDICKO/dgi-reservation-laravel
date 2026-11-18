<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MonCompteController extends Controller
{
    public function edit()
    {
        return view('mon-compte.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone' => ['nullable', 'regex:/^[0-9]{8}$/'],
        ], [
            'telephone.regex' => 'Le téléphone doit contenir exactement 8 chiffres.',
        ]);

        $user->fill([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
        ])->save();

        return redirect('/mon-compte')->with('success', 'Vos informations ont été mises à jour.');
    }

    /** Page « Changer mon mot de passe » (volontaire, avec le mot de passe actuel). */
    public function motDePasse()
    {
        return view('mon-compte.mot-de-passe');
    }

    public function changerMotDePasse(Request $request)
    {
        $request->validate([
            'mot_de_passe_actuel' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'string', 'min:8', 'different:mot_de_passe_actuel'],
        ], [
            'mot_de_passe_actuel.required'         => 'Veuillez saisir votre mot de passe actuel.',
            'mot_de_passe_actuel.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required'                    => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed'                   => 'Les mots de passe ne correspondent pas.',
            'password.min'                         => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.different'                   => 'Le nouveau mot de passe doit être différent de l\'ancien.',
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->doit_changer_mot_de_passe = false;
        $user->save();

        $request->session()->regenerate();

        return redirect('/mon-compte')->with('success', 'Votre mot de passe a été modifié avec succès.');
    }
}