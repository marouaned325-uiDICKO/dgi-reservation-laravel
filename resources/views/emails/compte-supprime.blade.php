<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; color: #222;">
    <h2 style="color: #b42318;">Suppression de votre compte</h2>

    <p>Bonjour {{ $nom }},</p>

    <p>Nous vous informons que votre compte sur la plateforme de réservation des salles de la Direction Générale des Impôts a été <strong>supprimé</strong> par un administrateur.</p>

    <p>Vous ne pouvez plus vous connecter à l'application.</p>

    @if($reservationsAnnulees > 0)
        <p>{{ $reservationsAnnulees }} demande(s) de réservation encore en attente ont été annulées.</p>
    @endif

    <p>Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur de votre direction.</p>

    <p style="margin-top: 24px;">Cordialement,<br>Direction Générale des Impôts</p>
</div>