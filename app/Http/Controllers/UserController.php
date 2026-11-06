<?php

namespace App\Http\Controllers;

use App\Mail\CompteCree;
use App\Models\Direction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const LIBELLES = [
        'admin'           => 'Admin',
        'gestionnaire'    => 'Gestionnaire',
        'agent'           => 'Agent demandeur',
    ];

    /** Un compte autre qu'Admin doit obligatoirement être rattaché à une direction. */
    private function verifierAffectation(): void
    {
        $u = auth()->user();
        abort_if($u->role !== 'admin' && ! $u->direction_id, 403, "Votre compte n'est rattaché à aucune direction.");
    }

    /** Directions que l'utilisateur connecté a le droit de choisir. */
    private function directionsAccessibles()
    {
        $u = auth()->user();
        $query = Direction::with('services')->orderBy('nom_direction');

        if ($u->role !== 'admin') {
            $query->where('id', $u->direction_id);
        }

        return $query->get();
    }

    /**
     * Rôles que l'utilisateur connecté a le droit d'attribuer.
     * Seul l'administrateur système peut créer un « Admin ».
     */
    private function rolesAutorises(): array
    {
        $u = auth()->user();

        if ($u->estPrincipal()) {
            $codes = ['agent', 'gestionnaire', 'admin'];
        } else {
            $codes = ['agent', 'gestionnaire'];
        }

        return collect($codes)->mapWithKeys(fn ($c) => [$c => self::LIBELLES[$c]])->all();
    }

    private function autoriser(User $cible): void
    {
        abort_unless(auth()->user()->peutGerer($cible), 403, "Vous ne pouvez pas gérer cet utilisateur.");
    }

    /**
     * Envoie un email sans jamais bloquer l'action en cas d'échec.
     * Retourne true si l'email est parti (ou mis en file d'attente), false sinon (l'erreur est écrite dans le log).
     */
    private function envoyerEmail(string $destinataire, Mailable $mail): bool
    {
        try {
            Mail::to($destinataire)->send($mail);
            return true;
        } catch (\Throwable $e) {
            Log::error("Échec de l'envoi de l'email à {$destinataire} : " . $e->getMessage());
            return false;
        }
    }

    /** Lien sécurisé (3 jours) qui connecte l'utilisateur puis l'envoie sur la page « Changer le mot de passe ». */
    private function lienActivation(User $user): string
    {
        return URL::temporarySignedRoute('compte.activation', now()->addDays(3), ['user' => $user->id]);
    }

    public function index()
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $query = User::with(['direction', 'service'])->withCount('reservations')->orderBy('name');

        if ($u->role !== 'admin') {
            $query->where('direction_id', $u->direction_id)->where('role', '!=', 'admin');
        }

        $utilisateurs = $query->get();

        return view('users.index', compact('utilisateurs'));
    }

    public function create()
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $directions = $this->directionsAccessibles();
        $roles = $this->rolesAutorises();
        $directionParDefaut = $u->role === 'admin' ? null : $u->direction_id;

        return view('users.create', compact('directions', 'roles', 'directionParDefaut'));
    }

    public function store(Request $request)
    {
        $this->verifierAffectation();
        $u = auth()->user();

        // Un « Administrateur » n'est rattaché à aucune direction ni service.
        // Il est toujours créé sans le statut d'administrateur système (est_principal = false).
        $estAdministrateur = $u->estPrincipal() && $request->input('role') === 'admin';

        // Un compte non-admin ne peut créer que dans sa propre direction
        $directionId = $estAdministrateur
            ? null
            : ($u->role === 'admin' ? $request->input('direction_id') : $u->direction_id);

        $request->merge([
            'direction_id' => $directionId,
            'service_id' => $estAdministrateur ? null : $request->input('service_id'),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'direction_id' => [Rule::requiredIf(! $estAdministrateur), 'nullable', 'exists:directions,id'],
            'service_id' => [Rule::requiredIf(! $estAdministrateur), 'nullable', Rule::exists('services', 'id')->where('direction_id', $directionId)],
            'role' => ['required', Rule::in(array_keys($this->rolesAutorises()))],
        ], [
            'direction_id.required' => 'Veuillez choisir une direction.',
            'service_id.required' => 'Veuillez choisir un service.',
            'service_id.exists' => "Ce service n'appartient pas à la direction choisie.",
            'role.in' => "Vous n'avez pas le droit d'attribuer ce rôle.",
        ]);

        // Aucun mot de passe n'est connu à la création : un mot de passe aléatoire inutilisable est posé,
        // l'utilisateur choisira le sien grâce au lien reçu par courriel.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'direction_id' => $validated['direction_id'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make(Str::random(40)),
            'doit_changer_mot_de_passe' => true,
        ]);

        // Courriel de création de compte, avec le lien pour choisir le mot de passe
        if ($this->envoyerEmail($user->email, new CompteCree($user, $this->lienActivation($user)))) {
            return redirect()->route('admin.users.index')
                ->with('success', "Utilisateur ajouté avec succès. Un lien pour choisir son mot de passe a été envoyé à {$user->email}.");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur ajouté avec succès, mais l'email n'a pas pu être envoyé. Utilisez « Réactiver » plus tard ou contactez l'administrateur système.");
    }

    public function edit(User $user)
    {
        $this->verifierAffectation();
        $this->autoriser($user);

        $directions = $this->directionsAccessibles();
        $roles = $this->rolesAutorises();

        // Garde le rôle actuel dans la liste, avec son vrai nom (Admin système / Admin)
        if ($user->role === 'admin') {
            $roles['admin'] = $user->libelle_role;
        } elseif (! isset($roles[$user->role]) && $user->role !== 'admin_direction') {
            $roles[$user->role] = self::LIBELLES[$user->role] ?? $user->role;
        }

        return view('users.edit', compact('user', 'directions', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->verifierAffectation();
        $this->autoriser($user);
        $u = auth()->user();

        // Le compte reste sans direction s'il est (ou devient) Administrateur
        $estAdministrateur = $u->role === 'admin' && $request->input('role') === 'admin';

        $directionId = $estAdministrateur
            ? null
            : ($u->role === 'admin' ? $request->input('direction_id') : $u->direction_id);

        $request->merge([
            'direction_id' => $directionId,
            'service_id' => $estAdministrateur ? null : $request->input('service_id'),
        ]);

        $rolesPermis = array_keys($this->rolesAutorises());
        $rolesPermis[] = $user->role; // le rôle actuel reste toujours accepté

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'direction_id' => [Rule::requiredIf(! $estAdministrateur), 'nullable', 'exists:directions,id'],
            'service_id' => [Rule::requiredIf(! $estAdministrateur), 'nullable', Rule::exists('services', 'id')->where('direction_id', $directionId)],
            'role' => ['required', Rule::in($rolesPermis)],
        ], [
            'direction_id.required' => 'Veuillez choisir une direction.',
            'service_id.required' => 'Veuillez choisir un service.',
            'service_id.exists' => "Ce service n'appartient pas à la direction choisie.",
            'role.in' => "Vous n'avez pas le droit d'attribuer ce rôle.",
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->telephone = $validated['telephone'] ?? null;
        $user->direction_id = $validated['direction_id'] ?? null;
        $user->service_id = $validated['service_id'] ?? null;

        // On ne change pas son propre rôle (évite de se retirer ses droits par erreur)
        if ($user->id !== $u->id) {
            $user->role = $validated['role'];
        }

        $user->save();

        // Aucun courriel à la modification
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    /** Réactive un compte expiré (jamais utilisé depuis plus de 3 jours) : 3 nouveaux jours, avec un nouveau lien si besoin. */
    public function reactiver(User $user)
    {
        $this->verifierAffectation();
        $this->autoriser($user);

        if (! $user->compteExpire()) {
            return redirect()->route('admin.users.index')
                ->with('error', "Ce compte n'est pas expiré.");
        }

        $user->forceFill(['created_at' => now()])->save();

        // Nouveau lien si le mot de passe n'a jamais été choisi
        if ($user->doit_changer_mot_de_passe) {
            $this->envoyerEmail($user->email, new CompteCree($user, $this->lienActivation($user)));
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Le compte de {$user->name} a été réactivé : il dispose de 3 nouveaux jours pour se connecter.");
    }

    public function destroy($id)
    {
        $user = User::find($id);

        // Compte déjà supprimé (page restée ouverte, double clic...) : on reste sur la liste avec un message
        if (! $user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cet utilisateur a déjà été supprimé.');
        }

        $this->verifierAffectation();
        $this->autoriser($user);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $nom = $user->name;

        $user->reservations()
            ->where('statut', 'en_attente')
            ->update(['statut' => 'refusee', 'motif_refus' => 'Compte utilisateur supprimé.']);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Le compte de {$nom} a été supprimé.");
    }
}