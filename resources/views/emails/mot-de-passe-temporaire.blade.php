<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f4f6f5;font-family:Arial,Helvetica,sans-serif;color:#222;">
    <div style="max-width:520px;margin:24px auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5eae7;">
        <div style="background:#1A8969;color:#ffffff;padding:18px 24px;border-bottom:4px solid #D4AF37;">
            <div style="font-size:16px;font-weight:bold;">DGI - Gestion des salles</div>
        </div>

        <div style="padding:24px;">
            <p style="margin:0 0 14px;font-size:14px;">Bonjour {{ $user->name }},</p>

            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Vous avez demandé la réinitialisation de votre mot de passe. Voici votre mot de passe temporaire :
            </p>

            <div style="text-align:center;margin:20px 0;">
                <span style="display:inline-block;padding:12px 26px;background:#fff8e1;border:1px dashed #D4AF37;border-radius:8px;font-size:22px;font-weight:bold;letter-spacing:2px;font-family:Consolas,monospace;color:#1a1a1a;">{{ $motDePasse }}</span>
            </div>

            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Connectez-vous avec votre adresse email et ce mot de passe. Il vous sera ensuite demandé de
                <strong>choisir un nouveau mot de passe</strong> personnel.
            </p>

            <p style="margin:0;font-size:12.5px;color:#888;line-height:1.5;">
                Si vous n'êtes pas à l'origine de cette demande, connectez-vous quand même pour changer votre mot de passe
                ou contactez l'administrateur.
            </p>
        </div>

        <div style="background:#f4f6f5;padding:12px 24px;font-size:11.5px;color:#999;text-align:center;">
            Direction Générale des Impôts - Burkina Faso
        </div>
    </div>
</body>
</html>