<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DGI - Gestion des Salles')</title>
    <meta name="description" content="Plateforme de gestion et de réservation des salles - Direction Générale des Impôts du Burkina Faso">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_dgi.jpeg') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background: #1A8969;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #d4af37;
            flex-wrap: wrap;
            gap: 10px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        .main { scroll-behavior: smooth; animation: apparitionPage 0.25s ease; }
        @keyframes apparitionPage {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        a:focus-visible, button:focus-visible { outline: 2px solid #d4af37; outline-offset: 2px; }

        .header h1 {
            font-size: 18px;
            font-weight: normal;
            letter-spacing: 0.5px;
        }

        .btn-connexion {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #1A8969;
            padding: 9px 22px;
            border-radius: 24px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 0.2px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
            border: 1.5px solid transparent;
            transition: 0.25s ease;
        }

        .btn-connexion .svg { width: 16px; height: 16px; }

        .btn-connexion:hover {
            background: #146e54;
            color: white;
            border-color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }

        .main {
            flex: 1;
            padding-top: 70px;
            padding-bottom: 60px;
            overflow-y: auto;
            height: 100vh;
        }

        .footer {
            background: #1A8969;
            color: white;
            font-size: 12px;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            border-top: 3px solid #d4af37;
            flex-wrap: wrap;
            gap: 4px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        .retour {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: #1A8969;
            background: white;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 24px;
            border: 1.5px solid #1A8969;
            box-shadow: 0 2px 6px rgba(26,137,105,0.1);
            transition: 0.25s ease;
        }
        .retour:hover {
            background: #1A8969;
            color: white;
            box-shadow: 0 4px 12px rgba(26,137,105,0.3);
            transform: translateY(-2px);
        }

        /* ===== LUMINOSITÉ JAUNE GLOBALE (cartes/formes uniquement, pages publiques) ===== */
        .mini-card,
        .stat-card,
        .demande,
        .table-wrapper,
        .graphique-card,
        .occupation-card,
        .resume-activite,
        .form-card,
        .salle-card,
        .banner {
            box-shadow: 0 2px 8px rgba(0,0,0,0.05), 0 16px 20px -16px rgba(212, 175, 55, 0.45);
        }

        /* ===== RESPONSIVE MOBILE ===== */
        @media (max-width: 600px) {
            .header {
                padding: 12px 16px;
                justify-content: space-between;
            }
            .header h1 {
                font-size: 13.5px;
            }
            .header img {
                height: 26px !important;
            }
            .btn-connexion {
                padding: 8px 16px;
                font-size: 12.5px;
            }

            .footer {
                padding: 10px 14px;
                font-size: 10.5px;
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 360px) {
            .header h1 {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img
                src="{{ asset('images/logo_dgi.jpeg') }}"
                alt="Logo DGI"
                style="height: 40px; width: 40px; object-fit: cover; border-radius: 50%;"
            >
            <h1>DGI - GESTION DE SALLES</h1>
        </div>

        <a href="{{ route('login') }}" class="btn-connexion">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Se connecter
        </a>
    </div>

    <div class="main">
        @yield('content')
    </div>

    <div class="footer">
        <span>© {{ date('Y') }} — Gestion des salles DGI</span>
        <span>Direction Générale des Impôts — Burkina Faso</span>
    </div>

</body>
</html>