<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'salle_id', 'direction', 'service', 'motif', 'statut', 'email',
        'date_reserv', 'date_fin', 'heure_debut', 'heure_fin', 'nombre',
    ];

    protected $casts = [
        'date_reserv' => 'date',
        'date_fin' => 'date',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /** Gestionnaire / administrateur qui a validé ou refusé la demande. */
    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    /** Réservations dont la période chevauche [dateDebut, dateFin] et le créneau [heureDebut, heureFin]. */
    public function scopeChevauche($query, $dateDebut, $dateFin, $heureDebut = null, $heureFin = null)
    {
        $debut = $heureDebut ?: '00:00:00';
        $fin   = $heureFin ?: '23:59:59';

        return $query
            ->whereDate('date_reserv', '<=', $dateFin)
            ->whereRaw('COALESCE(date_fin, date_reserv) >= ?', [$dateDebut])
            ->where(fn ($q) => $q->whereNull('heure_debut')->orWhere('heure_debut', '<', $fin))
            ->where(fn ($q) => $q->whereNull('heure_fin')->orWhere('heure_fin', '>', $debut));
    }

    public function getReferenceAttribute(): string
    {
        $annee = $this->created_at ? $this->created_at->format('Y') : date('Y');

        return 'RES-' . $annee . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}