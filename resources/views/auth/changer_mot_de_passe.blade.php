<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe - DGI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --vert: #1A8969;
            --vert-fonce: #1A8969;
            --vert-clair: #e8f5ee;
            --rouge: #c0392b;
            --orange: #e67e22;
            --gris: #6b7280;
            --bordure: #d1d5db;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #ffffff;
        }        
        
               .carte {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            border-top: 4px solid #D4AF37;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            overflow: hidden;
            animation: apparition .4s ease;
        }

        @keyframes apparition {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .entete {
            background: var(--vert);
            color: #fff;
            padding: 22px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .entete img { height: 44px; background: #fff; border-radius: 8px; padding: 4px; }
        .entete h2 { font-size: 15px; font-weight: 600; line-height: 1.3; }
        .entete small { font-size: 12px; opacity: .85; }
        .corps { padding: 28px; }
        .icone-cadenas {
            width: 60px; height: 60px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: var(--vert-clair);
            color: var(--vert);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }
        h1 { text-align: center; font-size: 20px; color: #1f2937; margin-bottom: 8px; }
        .intro { text-align: center; font-size: 13.5px; color: var(--gris); line-height: 1.5; margin-bottom: 22px; }
        .alerte {
            background: #fdecea; color: var(--rouge);
            border-left: 4px solid var(--rouge);
            padding: 10px 14px; border-radius: 6px;
            font-size: 13px; margin-bottom: 18px;
        }
        .alerte ul { padding-left: 18px; }
        .champ { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .saisie { position: relative; }
        .saisie > i.fa-lock {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--gris); font-size: 14px;
        }
        input[type=password], input[type=text] {
            width: 100%;
            padding: 12px 42px 12px 38px;
            border: 1.5px solid var(--bordure);
            border-radius: 8px;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
        }
        input:focus { outline: none; border-color: var(--vert); box-shadow: 0 0 0 3px rgba(11, 110, 58, .15); }
        input.invalide { border-color: var(--rouge); }
        input.valide { border-color: var(--vert); }
        .oeil {
            position: absolute; right: 10px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--gris); font-size: 15px; padding: 4px;
        }
        .oeil:hover { color: var(--vert); }
        .jauge { height: 6px; background: #e5e7eb; border-radius: 3px; margin-top: 8px; overflow: hidden; }
        .jauge div { height: 100%; width: 0; border-radius: 3px; transition: width .3s, background .3s; }
        .niveau { font-size: 12px; margin-top: 4px; color: var(--gris); }
        .criteres { list-style: none; margin-top: 10px; font-size: 12.5px; }
        .criteres li { color: var(--gris); margin-bottom: 4px; display: flex; align-items: center; gap: 7px; }
        .criteres li i { width: 14px; }
        .criteres li.ok { color: var(--vert); }
        .message-confirm { font-size: 12.5px; margin-top: 6px; min-height: 16px; }
        .btn {
            width: 100%;
            padding: 13px;
            border: none; border-radius: 8px;
            background: var(--vert); color: #fff;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .2s, transform .1s;
        }
        .btn:hover:not(:disabled) { background: var(--vert-fonce); }
        .btn:active:not(:disabled) { transform: scale(.98); }
        .btn:disabled { background: #9ca3af; cursor: not-allowed; }
        .pied { text-align: center; margin-top: 18px; font-size: 13px; }
        .pied button { background: none; border: none; color: var(--gris); cursor: pointer; font-size: 13px; }
        .pied button:hover { color: var(--rouge); text-decoration: underline; }
    </style>
</head>
<body>

<div class="carte">
    <div class="entete">
        <img src="{{ asset('images/logo_dgi.jpeg') }}" alt="Logo DGI">
        <div>
            <h2>Direction Générale des Impôts</h2>
            <small>Plateforme de gestion des salles</small>
        </div>
    </div>

    <div class="corps">
        <div class="icone-cadenas"><i class="fa-solid fa-shield-halved"></i></div>
        <h1>Changement de mot de passe requis</h1>
        <p class="intro">
            Bienvenue <strong>{{ auth()->user()->prenom ?? auth()->user()->name }}</strong>.
            Pour des raisons de sécurité, vous devez définir un nouveau mot de passe avant de continuer.
        </p>

        @if ($errors->any())
            <div class="alerte">
                <ul>
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

<form method="POST" action="{{ route('mot-de-passe.mettre-a-jour') }}" id="formMdp">            @csrf

            <div class="champ">
                <label for="password">Nouveau mot de passe</label>
                <div class="saisie">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Saisissez votre nouveau mot de passe"
                           autocomplete="new-password" required>
                    <button type="button" class="oeil" data-cible="password" aria-label="Afficher le mot de passe">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div class="jauge"><div id="barre"></div></div>
                <div class="niveau" id="niveau">Robustesse : —</div>

                <ul class="criteres">
                    <li id="c-longueur"><i class="fa-regular fa-circle"></i> Au moins 8 caractères</li>
                </ul>
                <div class="niveau">Saisir un mot de passe solide pour plus de sécurité.</div>            </div>

            <div class="champ">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <div class="saisie">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Ressaisissez le mot de passe"
                           autocomplete="new-password" required>
                    <button type="button" class="oeil" data-cible="password_confirmation" aria-label="Afficher le mot de passe">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div class="message-confirm" id="msgConfirm"></div>
            </div>

            <button type="submit" class="btn" id="btnValider" disabled>
                <i class="fa-solid fa-check"></i> Valider le nouveau mot de passe
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="pied">
            @csrf
            <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Se déconnecter</button>
        </form>
    </div>
</div>

<script>
    const MIN = 8;
    const mdp = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    const barre = document.getElementById('barre');
    const niveau = document.getElementById('niveau');
    const msgConfirm = document.getElementById('msgConfirm');
    const btn = document.getElementById('btnValider');
    const critereLongueur = document.getElementById('c-longueur');

    // Afficher / masquer le mot de passe
    document.querySelectorAll('.oeil').forEach(bouton => {
        bouton.addEventListener('click', () => {
            const champ = document.getElementById(bouton.dataset.cible);
            const icone = bouton.querySelector('i');
            const visible = champ.type === 'text';
            champ.type = visible ? 'password' : 'text';
            icone.className = visible ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
        });
    });

    function verifier() {
        const v = mdp.value;
        const longueurOk = v.length >= MIN;

        // Seule règle obligatoire : la longueur
        critereLongueur.classList.toggle('ok', longueurOk);
        critereLongueur.querySelector('i').className = longueurOk ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle';

        // Robustesse : indicative seulement, elle ne bloque rien
        let score = 0;
        if (longueurOk) score++;
        if (v.length >= 12) score++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;

        const niveaux = [
            { l: '0%',   c: '#e5e7eb', t: '—' },
            { l: '25%',  c: '#c0392b', t: 'Faible' },
            { l: '45%',  c: '#e67e22', t: 'Moyen' },
            { l: '65%',  c: '#f1c40f', t: 'Correct' },
            { l: '85%',  c: '#27ae60', t: 'Fort' },
            { l: '100%', c: '#1A8969', t: 'Très fort' },
        ];
        let n;
        if (v.length === 0) {
            n = niveaux[0];
        } else if (!longueurOk) {
            n = { l: '15%', c: '#c0392b', t: 'Trop court' };
        } else {
            n = niveaux[Math.max(score, 1)];
        }
        barre.style.width = n.l;
        barre.style.background = n.c;
        niveau.textContent = 'Robustesse : ' + n.t;
        niveau.style.color = v.length ? n.c : '';

        mdp.classList.toggle('valide', longueurOk);
        mdp.classList.toggle('invalide', v.length > 0 && !longueurOk);

        // Confirmation
        const c = confirmation.value;
        let correspond = false;
        if (c.length === 0) {
            msgConfirm.textContent = '';
            confirmation.classList.remove('valide', 'invalide');
        } else if (c === v) {
            correspond = true;
            msgConfirm.innerHTML = '<i class="fa-solid fa-circle-check"></i> Les mots de passe correspondent';
            msgConfirm.style.color = 'var(--vert)';
            confirmation.classList.add('valide');
            confirmation.classList.remove('invalide');
        } else {
            msgConfirm.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Les mots de passe ne correspondent pas';
            msgConfirm.style.color = 'var(--rouge)';
            confirmation.classList.add('invalide');
            confirmation.classList.remove('valide');
        }

        btn.disabled = !(longueurOk && correspond);
    }

    mdp.addEventListener('input', verifier);
    confirmation.addEventListener('input', verifier);

    // Empêcher le double clic à l'envoi
    document.getElementById('formMdp').addEventListener('submit', () => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';
    });
</script>

</body>
</html>