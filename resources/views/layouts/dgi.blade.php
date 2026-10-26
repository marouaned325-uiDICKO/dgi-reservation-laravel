<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1A8969">
    <title>@yield('title', 'DGI - Gestion des Salles')</title>
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
    }

    body {
        background: #f0f0f0;
    }


    .header {
        background: #1A8969;
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 63px;
        z-index: 100;
        border-bottom: 3px solid #d4af37;
    }

    .header h1 {
        font-size: 18px;
        font-weight: normal;
        letter-spacing: 0.5px;
    }

    .header-gauche {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bouton-menu-mobile {
        display: none;
        background: none;
        border: none;
        color: white;
        font-size: 22px;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 8px;
    }
    .bouton-menu-mobile:hover { background: rgba(255,255,255,0.15); }

    .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-right a.lien-entete {
        color: white;
        text-decoration: none;
        font-size: 13px;
    }

    .header-right a.lien-entete:hover {
        text-decoration: underline;
    }

    .logout-link {
        background: none;
        border: none;
        color: white;
        font-size: 13px;
        text-decoration: underline;
        cursor: pointer;
        font-family: inherit;
    }

    .container {
        display: flex;
        padding-top: 66px;
        padding-bottom: 44px;
        min-height: 100vh;
    }

    .sidebar {
        width: 240px;
        background: white;
        padding: 20px 0;
        position: fixed;
        top: 66px;
        bottom: 44px;
        left: 0;
        overflow-y: auto;
        z-index: 90;
        transition: transform 0.25s ease;
    }

    .fond-sidebar-mobile {
        display: none;
        position: fixed;
        top: 66px;
        bottom: 44px;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.4);
        z-index: 80;
    }

    .sidebar-title {
        padding: 5px 20px 10px;
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 0.6px;
    }

    .sidebar-title.espace {
        margin-top: 14px;
        padding-top: 16px;
        border-top: 1px solid #eee;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 2px 10px;
        padding: 8px 12px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        border-radius: 10px;
        border-left: 4px solid transparent;
        font-weight: 500;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .svg { width: 18px; height: 18px; flex-shrink: 0; }

    .sidebar a .ic {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 8px;
        background: #f1f4f2;
        color: #5b6b63;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .sidebar a:hover {
        background: #f3f8f5;
        color: #1A8969;
    }

    .sidebar a:hover .ic {
        background: #e0f0e8;
        color: #1A8969;
    }

    .sidebar a.active {
        background: #e8f5e9;
        color: #1A8969;
        border-left: 4px solid #d4af37;
        font-weight: bold;
    }

    .sidebar a.active .ic {
        background: #1A8969;
        color: white;
    }

    .sidebar::-webkit-scrollbar { width: 6px; }
    .sidebar::-webkit-scrollbar-thumb { background: #d9e6df; border-radius: 6px; }

    .main {
        flex: 1;
        margin-left: 240px;
        padding: 20px 30px;
        min-width: 0;
        animation: apparitionPage 0.25s ease;
    }

    @keyframes apparitionPage {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .footer {
        background: #1A8969;
        color: white;
        font-size: 12px;
        padding: 12px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 44px;
        z-index: 100;
        border-top: 3px solid #d4af37;
    }
    .menu-utilisateur {
    position: relative;
}

.bouton-utilisateur {
    background: none;
    border: 1.5px solid rgba(255,255,255,0.6);
    border-radius: 30px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    font-family: inherit;
    padding: 5px 16px 5px 6px;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    transition: 0.2s ease;
}

.avatar-initiale {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    color: #1A8969;
    font-weight: bold;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.cloche {
    position: relative;
    color: white;
    text-decoration: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s ease;
}
.cloche:hover { background: rgba(255,255,255,0.15); }
.cloche .pastille {
    position: absolute;
    top: 1px;
    right: 0;
    background: #d9534f;
    color: white;
    border-radius: 10px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #1A8969;
}

.bouton-utilisateur:hover {
    background: rgba(255,255,255,0.12);
    border-color: white;
}
.dropdown-utilisateur {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    width: 240px;
    z-index: 200;
    overflow: hidden;
}

.dropdown-utilisateur.ouvert {
    display: block;
}

.dropdown-entete {
    padding: 14px 16px;
}

.dropdown-nom {
    font-weight: bold;
    font-size: 14px;
    color: #1a1a1a;
}

.dropdown-email {
    font-size: 12px;
    color: #666;
    margin-top: 3px;
    word-break: break-all;
}

.dropdown-role {
    display: inline-block;
    font-size: 11px;
    font-weight: bold;
    color: #1A8969;
    background: #e8f5e9;
    padding: 3px 10px;
    border-radius: 20px;
    margin-top: 8px;
}

.dropdown-direction {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    margin-top: 10px;
    font-size: 12px;
    font-weight: 600;
    color: #146e54;
    line-height: 1.35;
}

.dropdown-direction .svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
    margin-top: 1px;
    color: #1A8969;
}

.dropdown-separateur {
    border-top: 1px solid #eee;
}

.dropdown-lien {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    text-align: left;
    padding: 10px 16px;
    font-size: 13px;
    color: #333;
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
}

.dropdown-lien .svg { width: 16px; height: 16px; opacity: 0.8; }

.dropdown-lien:hover {
    background: #f5f5f5;
}

.dropdown-deconnexion:hover {
    background: #fff3f3;
}

a:focus-visible, button:focus-visible {
    outline: 2px solid #d4af37;
    outline-offset: 2px;
}

.dropdown-deconnexion {
    color: #d9534f;
}

/* ===== LUMINOSITÉ JAUNE GLOBALE (cartes/formes uniquement, toutes pages) ===== */
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
@media (max-width: 900px) {
    .header {
        padding: 12px 16px;
    }

    .header h1 {
        font-size: 14px;
    }

    .bouton-menu-mobile {
        display: block;
    }

    .nom-utilisateur-texte {
        display: none;
    }

    .bouton-utilisateur {
        padding: 5px 10px 5px 6px;
    }

    .sidebar {
        transform: translateX(-100%);
        box-shadow: 4px 0 16px rgba(0,0,0,0.15);
        width: 220px;
    }

    .sidebar.ouverte {
        transform: translateX(0);
    }

    .fond-sidebar-mobile.visible {
        display: block;
    }

    .main {
        margin-left: 0;
        padding: 16px;
    }

    

    .footer {
        padding: 10px 14px;
        font-size: 10.5px;
        flex-direction: column;
        gap: 2px;
        height: auto;
        min-height: 40px;
        text-align: center;
    }

    .container {
        padding-bottom: 54px;
    }

    .dropdown-utilisateur {
        width: 200px;
        right: -10px;
    }
}

@media (max-width: 480px) {
    .header h1 {
        display: none;
    }
}

/* ===== NOTIFICATION DE SUCCÈS (fenêtre centrée) ===== */
.succes-fond {
    position: fixed; inset: 0; z-index: 2000;
    display: flex; align-items: center; justify-content: center;
    padding: 20px; background: rgba(0, 0, 0, 0.55);
    animation: succesFond .2s ease;
}
.succes-fond.ferme { opacity: 0; transition: opacity .2s ease; pointer-events: none; }
.succes-boite {
    position: relative; overflow: hidden;
    background: #fff; border-radius: 16px; border-top: 4px solid #D4AF37;
    max-width: 380px; width: 100%; padding: 30px 28px 26px; text-align: center;
    box-shadow: 0 12px 44px rgba(0, 0, 0, 0.28);
    animation: succesBoite .28s cubic-bezier(.2, .9, .3, 1.2);
}
.succes-icone {
    width: 58px; height: 58px; border-radius: 50%; margin: 0 auto 14px;
    background: #e8f5e9; color: #1A8969; border: 3px solid #D4AF37;
    display: flex; align-items: center; justify-content: center;
}
.succes-icone svg { width: 28px; height: 28px; }
.succes-boite h3 { margin: 0 0 6px; font-size: 17px; color: #1a1a1a; }
.succes-boite p { margin: 0 0 20px; font-size: 14px; color: #666; line-height: 1.5; }
.succes-boite button {
    background: #1A8969; color: #fff; border: none; border-radius: 20px;
    padding: 10px 34px; font-size: 13px; font-weight: 700; cursor: pointer;
    font-family: inherit; transition: .2s ease;
}
.succes-boite button:hover { background: #146e54; }
.succes-barre {
    position: absolute; left: 0; bottom: 0; height: 3px; width: 100%;
    background: linear-gradient(90deg, #1A8969, #D4AF37);
    transform-origin: left; animation: succesBarre 3.5s linear forwards;
}
@keyframes succesFond { from { opacity: 0; } to { opacity: 1; } }
@keyframes succesBoite { from { opacity: 0; transform: translateY(14px) scale(.94); } to { opacity: 1; transform: none; } }
@keyframes succesBarre { from { transform: scaleX(1); } to { transform: scaleX(0); } }

/* Étoile rouge des champs obligatoires */
.etoile { color: #d9534f; font-weight: 700; margin-left: 3px; }

/* ===== ACCESSIBILITÉ ET CONFORT ===== */
.lien-evitement {
    position: fixed; top: -60px; left: 12px; z-index: 3000;
    background: #fff; color: #1A8969; font-weight: 700; font-size: 13px;
    padding: 9px 16px; border-radius: 0 0 10px 10px; text-decoration: none;
    box-shadow: 0 4px 14px rgba(0,0,0,0.2); transition: top .15s ease;
}
.lien-evitement:focus { top: 0; }
.main:focus { outline: none; }

.bouton-utilisateur .caret { display: inline-block; transition: transform .2s ease; }
.bouton-utilisateur[aria-expanded="true"] .caret { transform: rotate(180deg); }

/* Bouton d'envoi : retour visuel pendant l'envoi (évite les doubles clics) */
button.envoi, input[type="submit"].envoi { opacity: .65; cursor: progress; }

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
}

/* ===== IMPRESSION : on ne garde que le contenu ===== */
@media print {
    .header, .sidebar, .footer, .fond-sidebar-mobile, .succes-fond, .lien-evitement { display: none !important; }
    .container { padding: 0; }
    .main { margin: 0; padding: 0; animation: none; }
    body { background: #fff; }
}
</style>
    @stack('styles')
</head>

<body>

    <a href="#contenu" class="lien-evitement">Aller au contenu</a>

    @php
        $nbNotifs = auth()->check()
            ? \App\Models\Notification::where('user_id', auth()->id())
                ->where('notif_lu', false)
                ->count()
            : 0;
    @endphp

    <div class="header">

        <div class="header-gauche">
            @auth
                <button type="button" class="bouton-menu-mobile" onclick="basculerSidebarMobile()" aria-label="Menu"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            @endauth
<img
    src="{{ asset('images/logo_dgi.jpeg') }}"
    alt="Logo DGI"
    style="height: 40px; width: 40px; object-fit: cover; border-radius: 50%;"
>
            <h1>DGI - GESTION DE SALLES</h1>
        </div>

<div class="header-right">
    @auth
        <a href="{{ route('notifications.index') }}" class="cloche" title="Notifications"
           aria-label="Notifications{{ $nbNotifs > 0 ? ' (' . $nbNotifs . ' non lue' . ($nbNotifs > 1 ? 's' : '') . ')' : '' }}">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            @if ($nbNotifs > 0)
                <span class="pastille">{{ $nbNotifs > 99 ? '99+' : $nbNotifs }}</span>
            @endif
        </a>

        <div class="menu-utilisateur">
            <button type="button" class="bouton-utilisateur" onclick="basculerMenuUtilisateur()"
                    aria-haspopup="true" aria-expanded="false" aria-controls="dropdownUtilisateur">
                <span class="avatar-initiale">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="nom-utilisateur-texte">{{ auth()->user()->name }} <span class="caret">▾</span></span>
            </button>

            <div id="dropdownUtilisateur" class="dropdown-utilisateur">
                <div class="dropdown-entete">
                    <div class="dropdown-nom">{{ auth()->user()->name }}</div>
                    <div class="dropdown-email">{{ auth()->user()->email }}</div>
                    <div class="dropdown-role">
                        {{ auth()->user()->libelle_role }}
                    </div>

                    @if (auth()->user()->direction)
                        <div class="dropdown-direction">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
                            <span>{{ auth()->user()->direction->nom_direction }}</span>
                        </div>
                    @endif
                </div>

                <div class="dropdown-separateur"></div>

                <a href="{{ route('compte.edit') }}" class="dropdown-lien"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Mon compte</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-lien dropdown-deconnexion"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg> Déconnexion</button>
                </form>
            </div>
        </div>
    @else
        <a href="{{ route('login') }}" class="lien-entete">Se connecter</a>
    @endauth
</div>

    </div>

    <div class="container">

        <div id="fondSidebarMobile" class="fond-sidebar-mobile" onclick="fermerSidebarMobile()"></div>

        <div id="sidebar" class="sidebar" role="navigation" aria-label="Navigation principale">

            @auth

                <div class="sidebar-title">
                    Menu principal
                </div>

                <a href="{{ route('dashboard') }}"
                   class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/></svg></span> Accueil
                </a>

                @if(auth()->user()->role === 'admin')

                    <a href="{{ route('admin.recapitulatif') }}"
                       class="{{ request()->routeIs('admin.recapitulatif') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></span> Récapitulatif
                    </a>

                @endif

                @if(in_array(auth()->user()->role, ['agent', 'gestionnaire', 'admin']))

                    <a href="{{ route('salles.liste') }}"
                       class="{{ request()->routeIs('salles.liste', 'reservations.create', 'reservations.edit') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></span> Salles
                    </a>

                    <a href="{{ route('reservations.mes_demandes') }}"
                       class="{{ request()->routeIs('reservations.mes_demandes') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></span> Mes demandes
                    </a>

                @endif

                @if(in_array(auth()->user()->role, ['gestionnaire', 'admin']))

                    <a href="{{ route('validation.index') }}"
                       class="{{ request()->routeIs('validation.*') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg></span> Les réservations
                    </a>

                @endif

                @if(in_array(auth()->user()->role, ['gestionnaire', 'admin']))

                    <div class="sidebar-title espace">
                        Gestion
                    </div>

                    <a href="{{ route('admin.salles.index') }}"
                       class="{{ request()->routeIs('admin.salles.*') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></span> Salles
                    </a>

                    <a href="{{ route('admin.equipements.index') }}"
                       class="{{ request()->routeIs('admin.equipements.*') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg></span> Équipements
                    </a>

                    <a href="{{ route('admin.motifs.index') }}"
                       class="{{ request()->routeIs('admin.motifs.*') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2"/></svg></span> Motifs
                    </a>

                @endif

                @if(auth()->user()->role === 'admin')

                    <div class="sidebar-title espace">
                        Administration
                    </div>

                    <a href="{{ route('admin.directions.index') }}"
                       class="{{ request()->routeIs('admin.directions.*') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-5h6v5"/><path d="M9 11h.01M15 11h.01"/></svg></span> Directions
                    </a>

                    <a href="{{ route('admin.services.index') }}"
                       class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/></svg></span> Services
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.8"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/></svg></span> Utilisateurs
                    </a>

                @endif

            @else

                <div class="sidebar-title">
                    Découvrir
                </div>

                <a href="{{ route('salles.galerie') }}"
                   class="{{ request()->routeIs('salles.galerie') ? 'active' : '' }}">
                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></span> Salles
                </a>

                <a href="{{ route('login') }}">
                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg></span> Connexion
                </a>

            @endauth

        </div>

        <main class="main" id="contenu" tabindex="-1">
            @yield('content')
        </main>

    </div>

    <div class="footer">
        <span>© {{ date('Y') }} — Gestion des salles DGI</span>
        <span>Direction Générale des Impôts — Burkina Faso</span>
    </div>
    <script>
function fermerMenuUtilisateur() {
    const menu = document.getElementById('dropdownUtilisateur');
    const bouton = document.querySelector('.bouton-utilisateur');
    if (menu) menu.classList.remove('ouvert');
    if (bouton) bouton.setAttribute('aria-expanded', 'false');
}

function basculerMenuUtilisateur() {
    const menu = document.getElementById('dropdownUtilisateur');
    const bouton = document.querySelector('.bouton-utilisateur');
    const ouvert = menu.classList.toggle('ouvert');
    bouton.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
}

document.addEventListener('click', function (event) {
    const menu = document.getElementById('dropdownUtilisateur');
    const bouton = document.querySelector('.bouton-utilisateur');
    if (menu && bouton && !menu.contains(event.target) && !bouton.contains(event.target)) {
        fermerMenuUtilisateur();
    }
});

function basculerSidebarMobile() {
    document.getElementById('sidebar').classList.toggle('ouverte');
    document.getElementById('fondSidebarMobile').classList.toggle('visible');
}

function fermerSidebarMobile() {
    document.getElementById('sidebar').classList.remove('ouverte');
    document.getElementById('fondSidebarMobile').classList.remove('visible');
}

document.querySelectorAll('#sidebar a').forEach(function (lien) {
    lien.addEventListener('click', fermerSidebarMobile);
});

// Le lien de la page en cours est signalé aux lecteurs d'écran
document.querySelectorAll('#sidebar a.active').forEach(function (lien) {
    lien.setAttribute('aria-current', 'page');
});

// Échap ferme le menu utilisateur et le menu mobile
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        fermerMenuUtilisateur();
        fermerSidebarMobile();
    }
});

// Évite les doubles envois de formulaire (double clic sur Enregistrer / Réserver)
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || e.defaultPrevented) return;
    if ((form.method || '').toLowerCase() === 'get') return;

    if (form.dataset.envoye === '1') {
        e.preventDefault();
        return;
    }
    form.dataset.envoye = '1';

    // Retour visuel sur le bouton, après la construction des données du formulaire
    setTimeout(function () {
        const bouton = e.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
        if (bouton) bouton.classList.add('envoi');
    }, 10);

    // Sécurité (téléchargement de fichier, envoi interrompu...) : on réactive après 8 secondes
    setTimeout(function () {
        delete form.dataset.envoye;
        form.querySelectorAll('.envoi').forEach(function (b) { b.classList.remove('envoi'); });
    }, 8000);
});

// Retour arrière du navigateur : on réactive les formulaires
window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    document.querySelectorAll('form[data-envoye]').forEach(function (f) { delete f.dataset.envoye; });
    document.querySelectorAll('.envoi').forEach(function (b) { b.classList.remove('envoi'); });
});
</script>

@if (session('success'))
    <div class="succes-fond" id="succesFond" role="alertdialog" aria-modal="true" aria-live="polite">
        <div class="succes-boite">
            <div class="succes-icone">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </div>
            <h3>Opération réussie</h3>
            <p id="succesTexte">{{ session('success') }}</p>
            <button type="button" onclick="fermerSucces()">OK</button>
            <div class="succes-barre"></div>
        </div>
    </div>
    <script>
        (function () {
            const fond = document.getElementById('succesFond');
            const message = document.getElementById('succesTexte').textContent.trim();
            const boutonOk = fond.querySelector('button');
            if (boutonOk) boutonOk.focus();

            // Masque les anciens bandeaux de succès affichés dans les pages
            document.querySelectorAll('body *').forEach(function (el) {
                if (fond.contains(el) || el.children.length > 0) return;
                if (el.textContent.trim() === message) {
                    const conteneur = el.closest('.alerte, .alerte-succes, .message, .alert, .alert-success, .success');
                    (conteneur || el).style.display = 'none';
                }
            });

            window.fermerSucces = function () {
                fond.classList.add('ferme');
                setTimeout(function () { fond.remove(); }, 220);
            };
            fond.addEventListener('click', function (e) { if (e.target === fond) fermerSucces(); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fermerSucces(); });
            setTimeout(fermerSucces, 3500);
        })();
    </script>
@endif

<script>
(function () {
    // Champs obligatoires de l'application (en plus de ceux qui ont l'attribut "required")
    const REQUIS = [
        'name', 'email', 'role', 'direction_id', 'service_id',
        'nom', 'localite', 'capacite', 'statut',
        'nom_direction', 'nom_service',
        'salle_id', 'motif', 'nombre', 'date_reserv', 'date_debut', 'date_fin',
        'password', 'password_confirmation'
    ];

    function trouverLabel(champ) {
        // 1. label lié par for/id
        if (champ.id) {
            const lie = document.querySelector('label[for="' + champ.id + '"]');
            if (lie) return lie;
        }
        // 2. label juste avant le champ
        let precedent = champ.previousElementSibling;
        while (precedent) {
            if (precedent.tagName === 'LABEL') return precedent;
            precedent = precedent.previousElementSibling;
        }
        // 3. premier label du bloc qui contient le champ
        const bloc = champ.closest('.form-group, .champ, .groupe, .field, .mb-3, .ligne-double > div') || champ.parentElement;
        return bloc ? bloc.querySelector('label') : null;
    }

    function marquerFormulaires() {
        document.querySelectorAll('form').forEach(function (form) {
            // Les formulaires de suppression sont ignorés
            if (form.querySelector('input[name="_method"][value="DELETE"]')) return;
            const modeModification = !!form.querySelector('input[name="_method"][value="PUT"], input[name="_method"][value="PATCH"]');

            form.querySelectorAll('input, select, textarea').forEach(function (champ) {
                const type = (champ.type || '').toLowerCase();
                if (['hidden', 'checkbox', 'radio', 'submit', 'button', 'file', 'search'].includes(type)) return;
                if (champ.hasAttribute('data-facultatif')) return;

                const nom = (champ.name || '').replace('[]', '');
                let obligatoire = champ.required || champ.hasAttribute('data-requis') || REQUIS.includes(nom);

                // En modification, le mot de passe est facultatif
                if (modeModification && nom.startsWith('password')) obligatoire = false;
                if (!obligatoire) return;

                const label = trouverLabel(champ);
                if (!label || label.dataset.etoile) return;
                // Un champ marqué « facultatif » dans son libellé n'est pas obligatoire
                if (/facultatif|optionnel/i.test(label.textContent)) return;

                const etoile = document.createElement('span');
                etoile.className = 'etoile';
                etoile.textContent = '*';
                etoile.setAttribute('aria-hidden', 'true');
                label.appendChild(etoile);
                label.dataset.etoile = '1';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', marquerFormulaires);
})();
</script>

@stack('scripts')
</body>
</html>