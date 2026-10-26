<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié - DGI</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_dgi.jpeg') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(160deg, #f4f7f5 0%, #e3ede7 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 24px 16px;
            color: #1a1a1a;
        }

        .conteneur { width: 100%; max-width: 440px; }

        .entete { text-align: center; margin-bottom: 22px; }
        .entete img {
            width: 84px; height: 84px; object-fit: contain; border-radius: 50%;
            background: white; padding: 4px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }
        .entete h1 { font-size: 15px; color: #1F6B41; margin-top: 12px; letter-spacing: 0.3px; }
        .entete p { font-size: 12px; color: #7a8a80; margin-top: 2px; }

        .carte {
            background: #1F6B41;
            border-radius: 14px;
            padding: 30px 32px 26px;
            color: white;
            border-top: 4px solid #d4af37;
            box-shadow: 0 10px 30px rgba(31,107,65,0.28), 0 2px 6px rgba(0,0,0,0.08);
        }
        .carte h2 { font-size: 20px; text-align: center; margin-bottom: 4px; }
        .carte .consigne {
            text-align: center; font-size: 13px; opacity: 0.85; margin-bottom: 22px; line-height: 1.5;
        }

        .statut {
            background: rgba(255,255,255,0.15); border-radius: 8px;
            padding: 10px 12px; font-size: 13px; margin-bottom: 16px;
        }

        label { display: block; font-size: 13px; font-weight: 600; margin: 0 0 6px; }

        .champ input[type="email"] {
            width: 100%; padding: 11px 14px; border-radius: 8px;
            border: 2px solid transparent; background: #f2f6f4; color: #1a1a1a;
            font-size: 14.5px; font-family: inherit; transition: 0.2s ease;
        }
        .champ input:focus {
            outline: none; background: white; border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212,175,55,0.25);
        }
        .champ.invalide input { border-color: #ff8a80; }

        .erreur { color: #ffd6d2; font-size: 12.5px; margin-top: 5px; font-weight: 500; }

        .btn-envoyer {
            width: 100%; margin-top: 22px; padding: 12px;
            border: none; border-radius: 8px; cursor: pointer;
            background: #d4af37; color: #1b2a21;
            font-size: 14px; font-weight: 800; letter-spacing: 0.8px; text-transform: uppercase;
            font-family: inherit; transition: 0.2s ease;
        }
        .btn-envoyer:hover { background: #e2bf4d; box-shadow: 0 4px 14px rgba(0,0,0,0.25); transform: translateY(-1px); }
        .btn-envoyer:disabled { opacity: 0.7; cursor: wait; transform: none; }

        .retour { text-align: center; margin-top: 18px; }
        .retour a { color: #1F6B41; font-size: 13px; font-weight: 600; text-decoration: none; }
        .retour a:hover { text-decoration: underline; }

        .pied { margin-top: 26px; font-size: 11.5px; color: #8a978f; text-align: center; }

        @media (max-width: 480px) {
            .carte { padding: 24px 20px 22px; }
        }
    </style>
</head>
<body>
    <div class="conteneur">
        <div class="entete">
            <img src="{{ asset('images/logo_dgi.jpeg') }}" alt="Direction Générale des Impôts">
            <h1>Direction Générale des Impôts</h1>
            <p>Gestion et réservation des salles</p>
        </div>

        <div class="carte">
            <h2>Mot de passe oublié</h2>
            <p class="consigne">
                Saisissez votre adresse email. Un mot de passe temporaire vous sera envoyé :
                vous devrez en choisir un nouveau dès votre prochaine connexion.
            </p>

            @if (session('status'))
                <div class="statut">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="formOubli">
                @csrf

                <label for="email">Adresse email</label>
                <div class="champ @error('email') invalide @enderror">
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="username" placeholder="exemple@dgi.bf">
                </div>
                @error('email') <div class="erreur">{{ $message }}</div> @enderror

                <button type="submit" class="btn-envoyer" id="btnEnvoyer">Recevoir un mot de passe temporaire</button>
            </form>
        </div>

        <div class="retour">
            <a href="{{ route('login') }}">Retour à la connexion</a>
        </div>

        <div class="pied">© {{ date('Y') }} Direction Générale des Impôts — Burkina Faso</div>
    </div>

    <script>
        // Évite le double envoi
        document.getElementById('formOubli').addEventListener('submit', function () {
            const b = document.getElementById('btnEnvoyer');
            b.disabled = true;
            b.textContent = 'Envoi en cours...';
        });
    </script>
</body>
</html>