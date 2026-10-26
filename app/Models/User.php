<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // est_principal n'est volontairement pas modifiable depuis un formulaire
    protected $fillable = [
        'name', 'email', 'password', 'telephone', 'role', 'direction_id', 'service_id', 'doit_changer_mot_de_passe', 'last_login_at'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'doit_changer_mot_de_passe' => 'boolean',
            'est_principal' => 'boolean',
        ];
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function estGestionnaire(): bool
    {
        return $this->role === 'gestionnaire';
    }

    /** Admin ou Admin système : gère toutes les directions. */
    public function estAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Le seul « Admin système » : compte de secours de la plateforme. */
    public function estPrincipal(): bool
    {
        return $this->role === 'admin' && (bool) $this->est_principal;
    }

    /** Nom du rôle affiché dans l'interface. */
    public function getLibelleRoleAttribute(): string
    {
        return match ($this->role) {
            'admin' => $this->est_principal ? 'Admin système' : 'Admin',
            'gestionnaire' => 'Gestionnaire',
            'agent' => 'Agent demandeur',
            default => (string) $this->role,
        };
    }

    /**
     * Cet utilisateur peut-il modifier / supprimer le compte $cible ?
     * - Admin système : tous les comptes (y compris les Admin : récupération)
     * - Admin : tous les comptes, sauf les autres Admin et l'Admin système
     */
    public function peutGerer(User $cible): bool
    {
        if ($this->estPrincipal()) {
            return true;
        }

        return $this->estAdmin() && ($cible->id === $this->id || $cible->role !== 'admin');
    }

    public function getReferenceAttribute()
    {
        $annee = $this->created_at->format('Y');
        $position = static::whereYear('created_at', $annee)
            ->where('id', '<=', $this->id)
            ->count();

        return 'User-' . $annee . '-' . str_pad($position, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Un compte jamais utilisé expire 3 jours après sa création.
     * Les administrateurs ne sont pas concernés.
     */
    public function compteExpire(): bool
    {
        return $this->role !== 'admin'
            && $this->last_login_at === null
            && $this->created_at !== null
            && $this->created_at->lt(now()->subDays(3));
    }
}