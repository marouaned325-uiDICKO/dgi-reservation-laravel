<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; color: #222;">
    <h2 style="color: #1b5e9a;">Modification de votre compte</h2>

    <p>Bonjour {{ $user->name }},</p>

    <p>Les informations de votre compte sur la plateforme de réservation des salles de la Direction Générale des Impôts ont été modifiées par un administrateur :</p>

    <table style="border-collapse: collapse; margin: 16px 0; width: 100%;">
        <tr style="background: #f2f4f7;">
            <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Information</th>
            <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Ancienne valeur</th>
            <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Nouvelle valeur</th>
        </tr>
        @foreach($changements as $c)
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>{{ $c['champ'] }}</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd; color: #777;">{{ $c['avant'] ?: '—' }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $c['apres'] ?: '—' }}</td>
            </tr>
        @endforeach
    </table>

    @if($nouveauMotDePasse)
        <p>Votre mot de passe a été réinitialisé. Voici votre nouveau mot de passe provisoire :</p>
        <p style="font-family: monospace; font-size: 16px; background: #f2f4f7; padding: 10px; display: inline-block;">{{ $nouveauMotDePasse }}</p>
        <p>Il vous sera demandé de le changer lors de votre prochaine connexion.</p>
    @endif

    <p style="margin: 24px 0;">
        <a href="{{ route('login') }}"
           style="background: #1b5e9a; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block;">
            Se connecter à la plateforme
        </a>
    </p>

    <p>Si vous n'êtes pas à l'origine de cette demande ou si une information vous semble incorrecte, contactez l'administrateur de votre direction.</p>

    <p style="margin-top: 24px;">Cordialement,<br>Direction Générale des Impôts</p>
</div>