<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - DGI</title>
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
            text-align: center; font-size: 13px; opacity: 0.85; margin-bottom: 22px;
        }

        .statut {
            background: rgba(255,255,255,0.15); border-radius: 8px;
            padding: 10px 12px; font-size: 13px; margin-bottom: 16px;
        }

        label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; }

        .champ { position: relative; }
        .champ input[type="email"], .champ input[type="password"], .champ input[type="text"] {
            width: 100%; padding: 11px 14px; border-radius: 8px;
            border: 2px solid transparent; background: #f2f6f4; color: #1a1a1a;
            font-size: 14.5px; font-family: inherit; transition: 0.2s ease;
        }
        .champ input:focus {
            outline: none; background: white; border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212,175,55,0.25);
        }
        .champ.invalide input { border-color: #ff8a80; }

        .champ .oeil {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; padding: 6px 8px;
            font-size: 12px; font-weight: 700; color: #1F6B41; border-radius: 6px;
        }
        .champ .oeil:hover { background: #e3ede7; }
        .champ.avec-oeil input { padding-right: 78px; }

        .erreur { color: #ffd6d2; font-size: 12.5px; margin-top: 5px; font-weight: 500; }

        .ligne-options {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 10px; margin-top: 16px;
        }
        .souvenir { display: flex; align-items: center; gap: 8px; margin: 0; font-weight: 400; cursor: pointer; }
        .souvenir input { width: 16px; height: 16px; accent-color: #d4af37; cursor: pointer; }
        .oublie { font-size: 13px; color: white; opacity: 0.9; }
        .oublie:hover { opacity: 1; }

        .btn-connexion {
            width: 100%; margin-top: 22px; padding: 12px;
            border: none; border-radius: 8px; cursor: pointer;
            background: #d4af37; color: #1b2a21;
            font-size: 14px; font-weight: 800; letter-spacing: 0.8px; text-transform: uppercase;
            font-family: inherit; transition: 0.2s ease;
        }
        .btn-connexion:hover { background: #e2bf4d; box-shadow: 0 4px 14px rgba(0,0,0,0.25); transform: translateY(-1px); }
        .btn-connexion:disabled { opacity: 0.7; cursor: wait; transform: none; }

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
            <h2>Connexion</h2>
            <p class="consigne">Saisissez votre adresse email et votre mot de passe</p>

            @if (session('status'))
                <div class="statut">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="formConnexion">
                @csrf

                <label for="email">Adresse email</label>
                <div class="champ @error('email') invalide @enderror">
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="username" placeholder="exemple@dgi.bf">
                </div>
                @error('email') <div class="erreur">{{ $message }}</div> @enderror

                <label for="password">Mot de passe</label>
                <div class="champ avec-oeil @error('password') invalide @enderror">
                    <input id="password" type="password" name="password"
                           required autocomplete="current-password" placeholder="••••••••">
                    <button type="button" class="oeil" id="basculerMdp" aria-label="Afficher ou masquer le mot de passe">Afficher</button>
                </div>
                @error('password') <div class="erreur">{{ $message }}</div> @enderror

                <div class="ligne-options">
                    <label class="souvenir" for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember">
                        Se souvenir de moi
                    </label>

                    @if (Route::has('password.request'))
                        <a class="oublie" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                    @endif
                </div>

                <button type="submit" class="btn-connexion" id="btnConnexion">Se connecter</button>
            </form>
        </div>

        <div class="retour">
            <a href="{{ url('/') }}">Retour à l'accueil</a>
        </div>

        <div class="pied">© {{ date('Y') }} Direction Générale des Impôts — Burkina Faso</div>
    </div>

    <script>
        // Afficher / masquer le mot de passe
        const champMdp = document.getElementById('password');
        const boutonOeil = document.getElementById('basculerMdp');
        boutonOeil.addEventListener('click', function () {
            const cache = champMdp.type === 'password';
            champMdp.type = cache ? 'text' : 'password';
            boutonOeil.textContent = cache ? 'Masquer' : 'Afficher';
        });

        // Évite le double envoi
        document.getElementById('formConnexion').addEventListener('submit', function () {
            const b = document.getElementById('btnConnexion');
            b.disabled = true;
            b.textContent = 'Connexion...';
        });
    </script>
</body>
</html>