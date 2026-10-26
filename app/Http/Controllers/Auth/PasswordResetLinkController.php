<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MotDePasseTemporaire;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Veuillez saisir votre adresse email.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
        ]);

        $email = mb_strtolower(trim($request->input('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            $motDePasse = $this->genererMotDePasse();

            try {
                // On envoie d'abord l'email : si l'envoi échoue, l'ancien mot de passe reste valable.
                Mail::to($user->email)->send(new MotDePasseTemporaire($user, $motDePasse));

                $user->forceFill([
                    'password' => Hash::make($motDePasse),
                    'doit_changer_mot_de_passe' => true,
                ])->save();
            } catch (\Throwable $e) {
                Log::error("Échec de l'envoi du mot de passe temporaire à {$user->email} : " . $e->getMessage());

                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => "L'email n'a pas pu être envoyé. Réessayez plus tard ou contactez l'administrateur."]);
            }
        }

        // Même message que le compte existe ou non (ne révèle pas quelles adresses sont enregistrées)
        return redirect()->route('login')
            ->with('status', "Si un compte existe avec cette adresse, un mot de passe temporaire vient de lui être envoyé par email.");
    }

    /** Mot de passe lisible : sans caractères ambigus (0/O, 1/l/I). */
    private function genererMotDePasse(int $longueur = 10): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $max = strlen($alphabet) - 1;
        $mdp = '';

        for ($i = 0; $i < $longueur; $i++) {
            $mdp .= $alphabet[random_int(0, $max)];
        }

        return $mdp;
    }
}