<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir mon mot de passe - DGI</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_dgi.jpeg') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f0f0f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .carte { background: #fff; width: 100%; max-width: 440px; border-radius: 14px; border-top: 4px solid #D4AF37; padding: 30px 28px; box-shadow: 0 2px 8px rgba(0,0,0,.06), 0 16px 20px -16px rgba(212,175,55,.45); }
        .logo { display: block; width: 64px; height: 64px; border-radius: 50%; object-fit: cover; margin: 0 auto 12px; }
        h1 { text-align: center; font-size: 20px; color: #1A8969; margin-bottom: 6px; }
        .sous-titre { text-align: center; font-size: 13px; color: #666; margin-bottom: 20px; line-height: 1.5; }
        .compte { text-align: center; font-size: 13px; background: #e8f5e9; color: #136a50; border-radius: 8px; padding: 8px; margin-bottom: 18px; word-break: break-all; }
        label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px; }
        .champ { position: relative; margin-bottom: 14px; }
        .champ input { width: 100%; padding: 11px 76px 11px 13px; border: 1.3px solid #ddd; border-radius: 9px; font-size: 14px; background: #fafafa; }
        .champ input:focus { outline: none; border-color: #1A8969; background: #fff; box-shadow: 0 0 0 3px rgba(26,137,105,.15); }
        .oeil { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #136a50; font-size: 12px; font-weight: 700; cursor: pointer; padding: 6px; }
        .criteres { list-style: none; font-size: 12.5px; color: #888; margin: -4px 0 16px; }
        .criteres li { padding: 2px 0 2px 20px; position: relative; }
        .criteres li::before { content: "\2022"; position: absolute; left: 6px; }
        .criteres li.ok { color: #1A8969; font-weight: 600; }
        .criteres li.ok::before { content: "\2713"; left: 3px; }
        .erreur { color: #d9534f; font-size: 12px; margin: -8px 0 12px; }
        .btn { width: 100%; padding: 12px; background: #1A8969; color: #fff; border: none; border-radius: 22px; font-size: 14px; font-weight: 700; cursor: pointer; }
        .btn:hover { background: #136a50; }
        .btn:disabled { opacity: .6; cursor: not-allowed; }
        a:focus-visible, button:focus-visible, input:focus-visible { outline: 2px solid #D4AF37; outline-offset: 2px; }
    </style>
</head>
<body>
    <div class="carte">
        <img class="logo" src="{{ asset('images/logo_dgi.jpeg') }}" alt="Logo DGI">
        <h1>Choisir mon mot de passe</h1>
        <p class="sous-titre">Bienvenue {{ $user->name }}. Définissez le mot de passe qui vous servira à vous connecter à la plateforme.</p>
        <div class="compte">Identifiant : {{ $user->email }}</div>

        <form method="POST" action="{{ request()->fullUrl() }}" id="formulaire">
            @csrf

            <label for="password">Nouveau mot de passe</label>
            <div class="champ">
                <input type="password" id="password" name="password" required autocomplete="new-password" autofocus>
                <button type="button" class="oeil" data-cible="password">Afficher</button>
            </div>
            @error('password') <div class="erreur">{{ $message }}</div> @enderror

            <ul class="criteres" id="criteres">
                <li data-regle="longueur">Au moins 8 caractères</li>
                <li data-regle="majuscule">Une majuscule</li>
                <li data-regle="minuscule">Une minuscule</li>
                <li data-regle="chiffre">Un chiffre</li>
            </ul>

            <label for="password_confirmation">Confirmer le mot de passe</label>
            <div class="champ">
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                <button type="button" class="oeil" data-cible="password_confirmation">Afficher</button>
            </div>

            <button type="submit" class="btn" id="envoyer">Enregistrer mon mot de passe</button>
        </form>
    </div>

    <script>
        const champ = document.getElementById('password');
        const regles = {
            longueur: v => v.length >= 8,
            majuscule: v => /[A-Z]/.test(v),
            minuscule: v => /[a-z]/.test(v),
            chiffre: v => /[0-9]/.test(v)
        };
        champ.addEventListener('input', function () {
            document.querySelectorAll('#criteres li').forEach(function (li) {
                li.classList.toggle('ok', regles[li.dataset.regle](champ.value));
            });
        });
        document.querySelectorAll('.oeil').forEach(function (b) {
            b.addEventListener('click', function () {
                const input = document.getElementById(b.dataset.cible);
                const cache = input.type === 'password';
                input.type = cache ? 'text' : 'password';
                b.textContent = cache ? 'Masquer' : 'Afficher';
            });
        });
        document.getElementById('formulaire').addEventListener('submit', function () {
            document.getElementById('envoyer').disabled = true;
        });
    </script>
</body>
</html>