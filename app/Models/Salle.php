<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $fillable = ['direction_id', 'nom', 'localite', 'secteur', 'statut', 'capacite', 'equipement', 'photo'];

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function estDisponible($date, $heureDebut, $heureFin): bool
    {
        return !$this->reservations()
            ->where('date_reserv', $date)
            ->whereIn('statut', ['en_attente', 'confirmee'])
            ->where('heure_debut', '<', $heureFin)
            ->where('heure_fin', '>', $heureDebut)
            ->exists();
    }

    /**
     * Réservation confirmée en cours à cet instant (null si la salle est libre).
     * Calculé en direct : une fois l'heure de fin passée, la salle redevient libre automatiquement.
     * Sans heures renseignées, la réservation occupe la salle toute la journée.
     */
    public function reservationEnCours()
    {
        $maintenant = now();
        $jour = $maintenant->toDateString();
        $heure = $maintenant->format('H:i');

        return $this->reservations()
            ->where('statut', 'confirmee')
            ->whereDate('date_reserv', '<=', $jour)
            ->whereDate('date_fin', '>=', $jour)
            ->where(function ($q) use ($heure) {
                $q->whereNull('heure_debut')
                  ->orWhereNull('heure_fin')
                  ->orWhere(function ($q2) use ($heure) {
                      $q2->where('heure_debut', '<=', $heure)
                         ->where('heure_fin', '>', $heure);
                  });
            })
            ->orderBy('heure_debut')
            ->first();
    }

    public function estOccupee(): bool
    {
        return $this->reservationEnCours() !== null;
    }

    public function getReferenceAttribute()
    {
        $annee = $this->created_at->format('Y');
        $position = static::whereYear('created_at', $annee)
            ->where('id', '<=', $this->id)
            ->count();

        return 'Salle-' . $annee . '-' . str_pad($position, 4, '0', STR_PAD_LEFT);
    }
}