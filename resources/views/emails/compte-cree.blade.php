<!DOCTYPE html>
<html lang="fr">
<body style="margin:0;padding:24px;background:#f0f0f0;font-family:Arial,sans-serif;color:#333;">
    <div style="max-width:520px;margin:0 auto;background:#ffffff;border-top:4px solid #D4AF37;border-radius:10px;padding:28px;">
        <h2 style="color:#1A8969;margin:0 0 14px;font-size:20px;">Votre compte a été créé</h2>

        <p style="font-size:14px;line-height:1.6;">Bonjour {{ $user->name }},</p>
        <p style="font-size:14px;line-height:1.6;">
            Un compte vient de vous être créé sur la plateforme de gestion et de réservation des salles
            de la Direction Générale des Impôts. Votre identifiant est : <strong>{{ $user->email }}</strong>.
        </p>
        <p style="font-size:14px;line-height:1.6;">
            Cliquez sur le bouton ci-dessous pour vous connecter. Vous serez invité à choisir votre mot de passe.
        </p>

        <p style="text-align:center;margin:26px 0;">
            <a href="{{ $lien }}" style="background:#1A8969;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:22px;font-weight:bold;font-size:14px;display:inline-block;">
                Me connecter et choisir mon mot de passe
            </a>
        </p>

        <p style="font-size:12.5px;color:#777;line-height:1.5;">
            Ce lien est valable 3 jours. Passé ce délai, demandez à un administrateur de réactiver votre compte.
        </p>
        <p style="font-size:12px;color:#999;word-break:break-all;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>{{ $lien }}
        </p>
    </div>
</body>
</html>