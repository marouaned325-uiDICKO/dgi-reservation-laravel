<div style="font-family: Arial, sans-serif; max-width: 600px;">
    <h2 style="color: #1a5e20;">Réservation validée</h2>
    <p>Bonjour {{ $reservation->user->name ?? '' }},</p>
    <p>Votre demande de réservation a été <strong>validée</strong> par l'administration de la DGI.</p>
    <ul>
        <li><strong>Salle :</strong> {{ $reservation->salle->nom ?? '' }}</li>
        <li><strong>Date :</strong> {{ $reservation->date ?? '' }}</li>
        <li><strong>Horaire :</strong> {{ $reservation->heure_debut ?? '' }} – {{ $reservation->heure_fin ?? '' }}</li>
    </ul>
    <p>Cordialement,<br>Direction Générale des Impôts</p>
</div>