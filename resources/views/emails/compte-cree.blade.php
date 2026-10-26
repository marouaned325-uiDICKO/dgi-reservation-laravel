<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: auto; color: #222; border: 1px solid #dfe8e4; border-radius: 8px; overflow: hidden;">
    <div style="background: #1A8969; color: #ffffff; padding: 18px 26px; border-bottom: 4px solid #D4AF37;">
        <h2 style="margin: 0; font-size: 18px;">Bienvenue sur la plateforme de réservation des salles</h2>
        <div style="font-size: 13px; opacity: .9; margin-top: 4px;">DGI Burkina Faso - Gestion des salles</div>
    </div>

    <div style="padding: 26px;">
        <p>Bonjour {{ $user->name }},</p>

        <p style="line-height: 1.55;">Un compte vient d'être créé pour vous sur la plateforme de gestion et de réservation des salles de la Direction Générale des Impôts.</p>

        <table style="border-collapse: collapse; margin: 16px 0; background: #e3f3ed; border-left: 4px solid #1A8969; width: 100%;">
            <tr>
                <td style="padding: 10px 12px;"><strong>Identifiant</strong></td>
                <td style="padding: 10px 12px;">{{ $user->email }}</td>
            </tr>
        </table>

        <p style="line-height: 1.55;">Cliquez sur le bouton ci-dessous pour vous connecter à la plateforme. Il vous sera demandé de choisir votre mot de passe.</p>

        <p style="margin: 24px 0; text-align: center;">
            <a href="{{ $lien }}"
               style="background: #1A8969; color: #ffffff; padding: 12px 26px; border-radius: 6px; text-decoration: none; display: inline-block; font-weight: bold;">
                Me connecter à la plateforme
            </a>
        </p>

        <p style="line-height: 1.55;"><strong>Important :</strong> ce lien est personnel, valable 3 jours, et ne doit être transmis à personne. Une fois votre mot de passe choisi, vous vous connecterez ensuite avec votre identifiant et ce mot de passe, depuis « Se connecter » en haut à gauche de la plateforme. Passé le délai de 3 jours, un administrateur devra réactiver votre compte.</p>

        <p style="margin-top: 24px;">Cordialement,<br>Direction Générale des Impôts</p>
    </div>
</div>