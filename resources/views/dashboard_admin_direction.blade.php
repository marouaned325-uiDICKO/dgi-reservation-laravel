@extends('layouts.dgi')

@section('title', 'Tableau de bord - DGI')

@section('content')
<style>
    .svg { width: 20px; height: 20px; flex-shrink: 0; }

    /* ===== BANNIÈRE ===== */
    .banner {
        background: linear-gradient(135deg, #1A8969 0%, #146e54 100%);
        border-radius: 14px;
        padding: 26px 30px;
        margin-bottom: 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: white;
        flex-wrap: wrap;
        gap: 14px;
        position: relative;
        overflow: hidden;
        border-bottom: 3px solid #d4af37;
    }
    .banner::after {
        content: "";
        position: absolute;
        right: -60px; top: -80px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .banner .texte { position: relative; z-index: 1; }
    .banner .texte h2 { font-size: 21px; margin-bottom: 6px; font-weight: 700; }
    .banner .texte p { color: #d9f0e3; font-size: 14px; }
    .banner .date-badge {
        position: relative; z-index: 1;
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.22);
        padding: 10px 20px;
        border-radius: 10px;
        text-align: center;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    .banner .date-badge .jour { font-size: 22px; font-weight: bold; display: block; letter-spacing: 0; }

    /* ===== TITRES DE SECTION ===== */
    .section-titre {
        font-size: 13px;
        font-weight: 700;
        color: #5b6b63;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin: 30px 0 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }
    .section-titre::before {
        content: "";
        width: 4px; height: 16px;
        background: #d4af37;
        border-radius: 2px;
        margin-right: 10px;
    }
    .section-titre .texte-titre { display: flex; align-items: center; flex: 1; }

    .btn-voir-tout {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        color: #1A8969;
        border: 1.3px solid #1A8969;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        text-transform: none;
        letter-spacing: 0;
        padding: 6px 14px;
        border-radius: 20px;
        transition: 0.2s ease;
    }
    .btn-voir-tout .svg { width: 14px; height: 14px; }
    .btn-voir-tout:hover { background: #1A8969; color: white; }

    /* ===== ACCÈS RAPIDE ===== */
    .liens-rapides {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }
    .lien-rapide {
        background: white;
        border-radius: 12px;
        padding: 18px;
        text-decoration: none;
        color: #222;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #eef1ef;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        min-width: 0;
    }
    .lien-rapide:hover {
        border-color: #1A8969;
        box-shadow: 0 6px 18px rgba(26, 137, 105, 0.12);
        transform: translateY(-2px);
    }
    .lien-rapide .pastille-icone {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: #e8f5e9;
        color: #1A8969;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: 0.2s ease;
    }
    .lien-rapide:hover .pastille-icone { background: #1A8969; color: white; }
    .lien-rapide .titre { font-size: 14px; font-weight: 700; color: #1a1a1a; }
    .lien-rapide .sous-titre { font-size: 12px; color: #888; margin-top: 2px; }

    /* ===== CARTES DE CHIFFRES ===== */
    .mini-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }
    .mini-card {
        background: white;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #eef1ef;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        min-width: 0;
    }
    .mini-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.07); }
    .mini-icone {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ton-vert   { background: #e8f5e9; color: #1A8969; }
    .ton-bleu   { background: #e3f2fd; color: #1565c0; }
    .ton-or     { background: #fff6dd; color: #b8860b; }
    .ton-rouge  { background: #fdeaea; color: #d9534f; }
    .ton-gris   { background: #eef1ef; color: #5b6b63; }
    .mini-card .valeur { font-size: 22px; font-weight: 800; color: #1a1a1a; line-height: 1.1; }
    .mini-card .libelle { font-size: 12px; color: #777; margin-top: 3px; }

    /* ===== DISPONIBILITÉ ===== */
    .occupation-card {
        background: white;
        border-radius: 12px;
        padding: 22px 26px;
        border: 1px solid #eef1ef;
    }
    .occupation-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 6px;
    }
    .occupation-header strong { font-size: 26px; color: #1A8969; }
    .occupation-header span { font-size: 12.5px; color: #888; }
    .barre-fond {
        background: #f0f0f0;
        border-radius: 20px;
        height: 10px;
        overflow: hidden;
        display: flex;
    }
    .barre-segment { height: 100%; transition: width 0.6s ease; }
    .legende-barre {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 14px;
        font-size: 12.5px;
        color: #555;
    }
    .legende-barre span { display: inline-flex; align-items: center; gap: 7px; }
    .legende-barre i { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

    /* ===== GRAPHIQUES ===== */
    .graphiques-grille {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .graphique-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #eef1ef;
    }
    .graphique-card h4 {
        font-size: 13px;
        color: #444;
        margin-bottom: 14px;
        text-align: center;
        font-weight: 700;
    }
    .graphique-canvas-wrapper { max-width: 230px; margin: 0 auto; }
    .graphique-vide { text-align: center; color: #999; font-size: 13px; padding: 40px 0; }

    /* ===== TABLEAU ===== */
    .table-wrapper {
        background: white;
        border-radius: 12px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #eef1ef;
    }
    table { width: 100%; background: white; border-collapse: collapse; }
    th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #f0f0f0; font-size: 13px; white-space: nowrap; }
    th {
        background: #1A8969;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.4px;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #f7faf8; }
    td { color: #333; }
    td.col-ref { font-weight: 700; color: #555; font-size: 12.5px; }

    .cellule-nom { display: flex; align-items: center; gap: 10px; }
    .avatar {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: #e8f5e9; color: #1A8969;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }

    .role-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .role-agent { background: #e8f5e9; color: #1A8969; }
    .role-gestionnaire { background: #e3f2fd; color: #1565c0; }
    .role-admin { background: #fdeaea; color: #d9534f; }
    .role-admin_direction { background: #fff6dd; color: #b8860b; }
    .empty-row { text-align: center; color: #999; padding: 30px; font-size: 13px; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1100px) {
        .liens-rapides { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 900px) {
        .graphiques-grille { grid-template-columns: 1fr; }
        .mini-stats { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .banner { padding: 18px; flex-direction: column; align-items: flex-start; }
        .banner .texte h2 { font-size: 17px; }
        .banner .texte p { font-size: 12.5px; }
        .banner .date-badge {
            align-self: stretch;
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 14px;
        }
        .banner .date-badge .jour { font-size: 15px; display: inline; }
        .liens-rapides { grid-template-columns: 1fr; }
        .mini-stats { grid-template-columns: 1fr; gap: 12px; }
        .mini-card { padding: 13px; }
        .occupation-card { padding: 16px; }
        .occupation-header strong { font-size: 21px; }
        .graphique-card { padding: 14px; }
        .graphique-canvas-wrapper { max-width: 190px; }
        th, td { padding: 9px 10px; font-size: 12px; }
    }

    /* ===== AJOUTS DASHBOARD DIRECTION ===== */
    .banner .direction-ligne {
        display: inline-flex; align-items: center; gap: 8px; margin-top: 10px;
        background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22);
        padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; color: white;
    }
    .banner .direction-ligne .svg { width: 16px; height: 16px; }

    .alerte-direction {
        display: flex; align-items: center; gap: 12px; padding: 13px 16px; margin-bottom: 20px;
        border-radius: 10px; font-size: 13.5px; font-weight: 600;
        background: #fff6dd; color: #8a6508; border: 1px solid #f0dca0;
    }
    .alerte-direction .svg { flex-shrink: 0; }
    .alerte-direction a { color: #8a6508; text-decoration: underline; }

    .deux-colonnes { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .liste-card { background: white; border-radius: 12px; padding: 20px; border: 1px solid #eef1ef; }
    .liste-card h4 { font-size: 13px; color: #444; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .liste-card h4 .svg { color: #1A8969; width: 16px; height: 16px; }
    .ligne-item {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 10px 0; border-bottom: 1px solid #f3f3f3; font-size: 13px;
    }
    .ligne-item:last-child { border-bottom: none; }
    .ligne-item .principal { font-weight: 700; color: #1a1a1a; }
    .ligne-item .secondaire { font-size: 12px; color: #888; margin-top: 2px; }
    .ligne-item .droite { text-align: right; font-size: 12px; color: #777; white-space: nowrap; }
    .puce-nb { background: #e8f5e9; color: #1A8969; font-weight: 700; font-size: 12px; padding: 3px 11px; border-radius: 20px; white-space: nowrap; }
    .puce-periode { background: #fff6dd; color: #8a6508; font-weight: 700; font-size: 11.5px; padding: 3px 10px; border-radius: 12px; white-space: nowrap; }
    .liste-vide { text-align: center; color: #999; font-size: 13px; padding: 26px 0; }

    @media (max-width: 900px) { .deux-colonnes { grid-template-columns: 1fr; } }
</style>

@php
    $totalSalles    = max($statsSalles['total'], 1);
    $pctDispo       = round($statsSalles['disponibles'] / $totalSalles * 100);
    $pctIndispo     = round($statsSalles['indisponibles'] / $totalSalles * 100);
    $pctMaintenance = max(100 - $pctDispo - $pctIndispo, 0);

    $libellePeriode = function ($r) {
        $debut = \Carbon\Carbon::parse($r->date_reserv);
        $fin = $r->date_fin ? \Carbon\Carbon::parse($r->date_fin) : $debut;
        return $debut->isSameDay($fin)
            ? $debut->format('d/m/Y')
            : $debut->format('d/m') . ' → ' . $fin->format('d/m/Y');
    };
    $libelleHeures = fn ($r) => ($r->heure_debut && $r->heure_fin)
        ? substr($r->heure_debut, 0, 5) . ' - ' . substr($r->heure_fin, 0, 5)
        : 'Journée entière';
@endphp

<div class="banner">
    <div class="texte">
        <h2>Bonjour, {{ auth()->user()->name }}</h2>
        <p>Administrateur de direction — suivi des salles, des réservations et des utilisateurs de votre direction.</p>
        <span class="direction-ligne">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
            {{ $direction->nom_direction ?? 'Direction non renseignée' }}
        </span>
    </div>
    <div class="date-badge">
        <span class="jour">{{ \Carbon\Carbon::now()->translatedFormat('d M') }}</span>
        {{ \Carbon\Carbon::now()->translatedFormat('Y') }}
    </div>
</div>

@if ($statsUtilisateurs['gestionnaires'] === 0)
    <div class="alerte-direction">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
        <span>Aucun gestionnaire dans votre direction : les demandes de réservation de vos salles ne peuvent pas être validées. <a href="{{ route('admin.users.create') }}">Ajouter un gestionnaire</a></span>
    </div>
@endif

@if ($statsReservations['en_attente'] > 0)
    <div class="alerte-direction">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        <span>{{ $statsReservations['en_attente'] }} demande(s) de réservation en attente de validation par vos gestionnaires.</span>
    </div>
@endif

<div class="section-titre"><span class="texte-titre">Accès rapide</span></div>
<div class="liens-rapides">
    <a href="{{ route('admin.users.index') }}" class="lien-rapide">
        <span class="pastille-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.8"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/></svg></span>
        <span><div class="titre">Utilisateurs</div><div class="sous-titre">Agents et gestionnaires</div></span>
    </a>
    <a href="{{ route('admin.services.index') }}" class="lien-rapide">
        <span class="pastille-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/></svg></span>
        <span><div class="titre">Services</div><div class="sous-titre">Services de la direction</div></span>
    </a>
    <a href="{{ route('admin.salles.index') }}" class="lien-rapide">
        <span class="pastille-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></span>
        <span><div class="titre">Salles</div><div class="sous-titre">Gestion et disponibilité</div></span>
    </a>
    <a href="{{ route('admin.users.create') }}" class="lien-rapide">
        <span class="pastille-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
        <span><div class="titre">Nouvel utilisateur</div><div class="sous-titre">Créer un compte</div></span>
    </a>
</div>

<div class="section-titre"><span class="texte-titre">Activité des réservations</span></div>
<div class="mini-stats">
    <div class="mini-card">
        <div class="mini-icone ton-gris"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg></div>
        <div><div class="valeur">{{ $statsReservations['total'] }}</div><div class="libelle">Réservations au total</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-or"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
        <div><div class="valeur">{{ $statsReservations['en_attente'] }}</div><div class="libelle">En attente</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg></div>
        <div><div class="valeur">{{ $statsReservations['confirmee'] }}</div><div class="libelle">Confirmées</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-bleu"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></div>
        <div><div class="valeur">{{ $sallesOccupeesAujourdhui }}</div><div class="libelle">Salles occupées aujourd'hui</div></div>
    </div>
</div>

<div class="section-titre"><span class="texte-titre">Planning</span></div>
<div class="deux-colonnes">
    <div class="liste-card">
        <h4>
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            Occupées aujourd'hui
        </h4>
        @forelse ($occupeesAujourdhui as $r)
            <div class="ligne-item">
                <div>
                    <div class="principal">{{ $r->salle->nom ?? '-' }}</div>
                    <div class="secondaire">{{ $r->utilisateur->name ?? '-' }}</div>
                </div>
                <div class="droite">{{ $libelleHeures($r) }}</div>
            </div>
        @empty
            <div class="liste-vide">Aucune salle occupée aujourd'hui.</div>
        @endforelse
    </div>

    <div class="liste-card">
        <h4>
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
            Prochaines réservations confirmées
        </h4>
        @forelse ($prochaines as $r)
            <div class="ligne-item">
                <div>
                    <div class="principal">{{ $r->salle->nom ?? '-' }}</div>
                    <div class="secondaire">{{ $r->utilisateur->name ?? '-' }} · {{ $libelleHeures($r) }}</div>
                </div>
                <span class="puce-periode">{{ $libellePeriode($r) }}</span>
            </div>
        @empty
            <div class="liste-vide">Aucune réservation à venir.</div>
        @endforelse
    </div>
</div>

<div class="section-titre"><span class="texte-titre">Répartition</span></div>
<div class="graphiques-grille">
    <div class="graphique-card">
        <h4>Salles par statut</h4>
        @if ($statsSalles['total'] > 0)
            <div class="graphique-canvas-wrapper"><canvas id="graphSalles"></canvas></div>
        @else
            <div class="graphique-vide">Aucune salle dans votre direction.</div>
        @endif
    </div>
    <div class="graphique-card">
        <h4>Réservations par statut</h4>
        @if ($statsReservations['total'] > 0)
            <div class="graphique-canvas-wrapper"><canvas id="graphReservations"></canvas></div>
        @else
            <div class="graphique-vide">Aucune réservation pour le moment.</div>
        @endif
    </div>
</div>

<div class="section-titre"><span class="texte-titre">Taux de disponibilité des salles</span></div>
<div class="occupation-card">
    <div class="occupation-header">
        <strong>{{ $pctDispo }}%</strong>
        <span>{{ $statsSalles['disponibles'] }} disponible(s) sur {{ $statsSalles['total'] }} salle(s)</span>
    </div>
    <div class="barre-fond">
        <div class="barre-segment" style="width: {{ $pctDispo }}%; background:#1A8969;"></div>
        <div class="barre-segment" style="width: {{ $pctIndispo }}%; background:#d9534f;"></div>
        <div class="barre-segment" style="width: {{ $pctMaintenance }}%; background:#b8860b;"></div>
    </div>
    <div class="legende-barre">
        <span><i style="background:#1A8969;"></i>Disponibles ({{ $statsSalles['disponibles'] }})</span>
        <span><i style="background:#d9534f;"></i>Indisponibles ({{ $statsSalles['indisponibles'] }})</span>
        <span><i style="background:#b8860b;"></i>Maintenance ({{ $statsSalles['maintenance'] }})</span>
    </div>
</div>

<div class="section-titre"><span class="texte-titre">Utilisateurs et services</span></div>
<div class="mini-stats">
    <div class="mini-card">
        <div class="mini-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <div><div class="valeur">{{ $statsUtilisateurs['agents'] }}</div><div class="libelle">Agents</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-bleu"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg></div>
        <div><div class="valeur">{{ $statsUtilisateurs['gestionnaires'] }}</div><div class="libelle">Gestionnaires</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-gris"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.8"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/></svg></div>
        <div><div class="valeur">{{ $statsUtilisateurs['total'] }}</div><div class="libelle">Utilisateurs au total</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-or"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/></svg></div>
        <div><div class="valeur">{{ $services->count() }}</div><div class="libelle">Services</div></div>
    </div>
</div>

<div class="section-titre"><span class="texte-titre">Services de la direction</span></div>
<div class="liste-card">
    @forelse ($services as $service)
        <div class="ligne-item">
            <div class="principal">{{ $service->nom_service }}</div>
            <span class="puce-nb">{{ $nbParService[$service->id] ?? 0 }} utilisateur(s)</span>
        </div>
    @empty
        <div class="liste-vide">Aucun service dans votre direction.</div>
    @endforelse
</div>

<div class="section-titre">
    <span class="texte-titre">Derniers utilisateurs de la direction</span>
    <a href="{{ route('admin.users.index') }}" class="btn-voir-tout">Voir les utilisateurs <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
</div>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Service</th>
                <th>Rôle</th>
                <th>Inscrit le</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($derniersUtilisateurs as $u)
            <tr>
                <td class="col-ref">{{ $u->reference }}</td>
                <td>
                    <div class="cellule-nom">
                        <span class="avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                        {{ $u->name }}
                    </div>
                </td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->telephone ?? '-' }}</td>
                <td>{{ $u->service->nom_service ?? '-' }}</td>
                <td><span class="role-badge role-{{ $u->role }}">{{ ['admin_direction' => 'Admin direction', 'gestionnaire' => 'Gestionnaire', 'agent' => 'Agent'][$u->role] ?? ucfirst($u->role) }}</span></td>
                <td>{{ $u->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty-row">Aucun utilisateur pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const optionsCommunes = {
        responsive: true,
        cutout: '68%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 }, padding: 12 }
            }
        }
    };

    const canvasSalles = document.getElementById('graphSalles');
    if (canvasSalles) {
        new Chart(canvasSalles, {
            type: 'doughnut',
            data: {
                labels: ['Disponibles', 'Indisponibles', 'Maintenance'],
                datasets: [{
                    data: [
                        {{ $statsSalles['disponibles'] }},
                        {{ $statsSalles['indisponibles'] }},
                        {{ $statsSalles['maintenance'] }}
                    ],
                    backgroundColor: ['#1A8969', '#d9534f', '#b8860b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: optionsCommunes
        });
    }

    const canvasReservations = document.getElementById('graphReservations');
    if (canvasReservations) {
        new Chart(canvasReservations, {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'Confirmées', 'Refusées'],
                datasets: [{
                    data: [
                        {{ $statsReservations['en_attente'] }},
                        {{ $statsReservations['confirmee'] }},
                        {{ $statsReservations['refusee'] }}
                    ],
                    backgroundColor: ['#b8860b', '#1A8969', '#d9534f'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: optionsCommunes
        });
    }
</script>
@endsection