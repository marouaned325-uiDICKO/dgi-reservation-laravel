@extends('layouts.dgi')

@section('title', 'Tableau de bord - DGI')

@section('content')
@php
    $aujourdhui = \Carbon\Carbon::today();

    // Demandes en attente : les dates dépassées passent en dernier, puis les plus anciennes d'abord
    $aTraiter = $demandesEnAttente
        ->sortBy(function ($d) use ($aujourdhui) {
            $fin = $d->date_fin ? \Carbon\Carbon::parse($d->date_fin) : \Carbon\Carbon::parse($d->date_reserv);
            return [$fin->lt($aujourdhui) ? 1 : 0, $d->created_at];
        })
        ->values();
    $aTraiterAffichees = $aTraiter->take(8);

    // Répartition des demandes
    $totalDemandes = max(1, $stats['en_cours'] + $stats['validees'] + $stats['rejetees']);
    $pctAttente = round($stats['en_cours'] / $totalDemandes * 100);
    $pctValidees = round($stats['validees'] / $totalDemandes * 100);
    $pctRejetees = max(0, 100 - $pctAttente - $pctValidees);

    // Occupation des salles aujourd'hui
    $sallesOccupees = $reservationsAujourdhui->pluck('salle_id')->filter()->unique()->count();
    $pctOccupation = $stats['total_salles'] > 0
        ? min(100, round($sallesOccupees / $stats['total_salles'] * 100))
        : 0;
@endphp

<style>
    .svg { width: 20px; height: 20px; flex-shrink: 0; }

    .banner {
        background: linear-gradient(135deg, #1A8969 0%, #146e54 100%);
        border-radius: 14px; padding: 26px 30px; margin-bottom: 26px;
        display: flex; align-items: center; justify-content: space-between;
        color: white; flex-wrap: wrap; gap: 14px;
        position: relative; overflow: hidden; border-bottom: 3px solid #D4AF37;
    }
    .banner::after {
        content: ""; position: absolute; right: -60px; top: -80px;
        width: 240px; height: 240px; border-radius: 50%; background: rgba(255,255,255,0.06);
    }
    .banner .texte { position: relative; z-index: 1; }
    .banner .texte h2 { font-size: 21px; margin-bottom: 6px; font-weight: 700; }
    .banner .texte p { color: #d9f0e3; font-size: 14px; }
    .banner .date-badge {
        position: relative; z-index: 1;
        background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22);
        padding: 10px 20px; border-radius: 10px; text-align: center;
        font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; flex-shrink: 0;
    }
    .banner .date-badge .jour { font-size: 22px; font-weight: bold; display: block; letter-spacing: 0; }

    .section-titre {
        font-size: 13px; font-weight: 700; color: #5b6b63;
        text-transform: uppercase; letter-spacing: 0.7px;
        margin: 30px 0 14px; display: flex; align-items: center; gap: 0;
    }
    .section-titre::before {
        content: ""; width: 4px; height: 16px; background: #D4AF37; border-radius: 2px; margin-right: 10px;
    }
    .section-titre .compteur {
        margin-left: 10px; background: #fff6dd; color: #b8860b; font-size: 12px;
        padding: 2px 10px; border-radius: 12px; letter-spacing: 0;
    }

    /* ===== ACCÈS RAPIDES ===== */
    .acces-rapides { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
    .acces {
        display: inline-flex; align-items: center; gap: 8px;
        background: white; color: #1A8969; text-decoration: none; font-size: 13px; font-weight: 700;
        padding: 9px 18px; border: 1.3px solid #cfe5dc; border-radius: 22px; transition: 0.2s ease;
    }
    .acces .svg { width: 16px; height: 16px; }
    .acces:hover { background: #1A8969; color: white; border-color: #1A8969; }

    .stats, .mini-stats { display: grid; gap: 15px; }
    .stats { grid-template-columns: repeat(4, 1fr); }
    .mini-stats { grid-template-columns: repeat(3, 1fr); }
    .mini-stats.deux { grid-template-columns: repeat(2, 1fr); }
    .stat-card, .mini-card {
        background: white; border-radius: 12px; padding: 16px 18px;
        display: flex; align-items: center; gap: 14px;
        border: 1px solid #eef1ef; transition: transform 0.2s ease, box-shadow 0.2s ease; min-width: 0;
        text-decoration: none; color: inherit;
    }
    .stat-card:hover, .mini-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.07); }
    .stat-card.attention { border-color: #ecd9a0; background: #fffdf5; }
    .stat-icone, .mini-icone {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .ton-vert  { background: #e8f5e9; color: #1A8969; }
    .ton-bleu  { background: #e3f2fd; color: #1565c0; }
    .ton-or    { background: #fff6dd; color: #b8860b; }
    .ton-rouge { background: #fdeaea; color: #d9534f; }
    .ton-gris  { background: #eef1ef; color: #5b6b63; }
    .stat-card .number, .mini-card .valeur { font-size: 22px; font-weight: 800; color: #1a1a1a; line-height: 1.1; }
    .stat-card .label, .mini-card .libelle { font-size: 12px; color: #777; margin-top: 3px; }

    /* ===== RÉPARTITION DES DEMANDES ===== */
    .carte-blanche { background: white; border-radius: 12px; border: 1px solid #eef1ef; padding: 18px 22px; margin-top: 15px; }
    .barre-repartition { display: flex; height: 12px; border-radius: 8px; overflow: hidden; background: #eef1ef; }
    .barre-repartition span { display: block; height: 100%; }
    .seg-attente { background: #D4AF37; }
    .seg-validee { background: #1A8969; }
    .seg-refusee { background: #d9534f; }
    .legende { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 12px; font-size: 12.5px; color: #666; }
    .legende span { display: inline-flex; align-items: center; gap: 7px; }
    .legende i { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

    /* ===== DEMANDES À TRAITER ===== */
    .liste-demandes { background: white; border-radius: 12px; border: 1px solid #eef1ef; overflow: hidden; }
    .demande {
        display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1.6fr) auto;
        gap: 16px; align-items: center; padding: 16px 22px; border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s ease;
    }
    .demande:last-child { border-bottom: none; }
    .demande:hover { background: #fafcfb; }
    .demande.depassee { opacity: .72; }

    .demande-qui { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .avatar {
        width: 40px; height: 40px; border-radius: 50%; background: #e8f5e9; color: #1A8969;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 15px; flex-shrink: 0;
    }
    .demande-nom { font-size: 14px; font-weight: 700; color: #1a1a1a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .demande-sous { font-size: 12px; color: #888; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .demande-detail { min-width: 0; }
    .demande-salle { font-size: 14px; font-weight: 700; color: #1A8969; }
    .demande-infos { display: flex; flex-wrap: wrap; gap: 6px 14px; margin-top: 4px; font-size: 12.5px; color: #666; }
    .demande-infos span { display: inline-flex; align-items: center; gap: 5px; }
    .demande-infos .svg { width: 14px; height: 14px; color: #8a9a92; }

    .tag-motif {
        display: inline-block; background: #eef1ef; color: #555; font-size: 11.5px; font-weight: 600;
        padding: 2px 9px; border-radius: 10px; margin-left: 8px; vertical-align: middle;
    }
    .badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px; margin-left: 6px; vertical-align: middle; }
    .badge-urgent { background: #fff6dd; color: #b8860b; border: 1px solid #ecd9a0; }
    .badge-depasse { background: #eef1ef; color: #5b6b63; border: 1px solid #d9e0dc; }

    .alerte-conflit {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 8px;
        background: #fdeaea; color: #c0392b; font-size: 11.5px; font-weight: 700;
        padding: 4px 10px; border-radius: 10px;
    }
    .alerte-conflit .svg { width: 13px; height: 13px; }

    .btn-traiter {
        display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
        background: #1A8969; color: white; text-decoration: none; font-size: 12.5px; font-weight: 700;
        padding: 8px 18px; border-radius: 20px; border: 1.3px solid #1A8969;
        box-shadow: 0 2px 6px rgba(26,137,105,0.25); transition: 0.2s ease;
    }
    .btn-traiter .svg { width: 14px; height: 14px; }
    .btn-traiter:hover { background: #146e54; border-color: #146e54; }

    .pied-liste {
        display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
        padding: 14px 22px; background: #fafcfb; border-top: 1px solid #f0f0f0; font-size: 13px; color: #777;
    }
    .vide-demandes { text-align: center; padding: 38px 20px; }
    .vide-demandes .svg { width: 38px; height: 38px; color: #b8dccc; margin-bottom: 8px; }
    .vide-demandes h3 { margin: 0 0 4px; color: #444; font-size: 15px; }
    .vide-demandes p { margin: 0; color: #999; font-size: 13px; }

    /* ===== OCCUPATION ET RÉSERVATIONS DU JOUR ===== */
    .occupation-tete { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
    .occupation-tete strong { font-size: 22px; color: #1A8969; }
    .occupation-tete span { font-size: 13px; color: #777; }
    .jauge { height: 12px; background: #eef1ef; border-radius: 8px; overflow: hidden; }
    .jauge div { height: 100%; background: linear-gradient(90deg, #1A8969, #D4AF37); border-radius: 8px; transition: width .4s ease; }

    .resume-activite { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #eef1ef; margin-top: 15px; }
    .resa-ligne {
        display: grid; grid-template-columns: 92px minmax(0, 1.4fr) minmax(0, 1.2fr) auto;
        gap: 14px; align-items: center; padding: 14px 22px; border-bottom: 1px solid #f0f0f0;
    }
    .resa-ligne:last-child { border-bottom: none; }
    .resa-heure { font-size: 13px; font-weight: 800; color: #1A8969; background: #e8f5e9; padding: 5px 10px; border-radius: 8px; text-align: center; }
    .resa-salle { font-size: 14px; font-weight: 700; color: #1a1a1a; }
    .resa-sous { font-size: 12px; color: #888; margin-top: 2px; }
    .resa-qui { font-size: 13px; color: #555; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .btn-voir {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12.5px; font-weight: 700; color: #1A8969; text-decoration: none;
        padding: 7px 14px; border: 1.3px solid #1A8969; border-radius: 20px;
        transition: 0.2s ease; white-space: nowrap;
    }
    .btn-voir .svg { width: 14px; height: 14px; }
    .btn-voir:hover { background: #1A8969; color: white; }

    @media (max-width: 900px) {
        .stats { grid-template-columns: repeat(2, 1fr); }
        .mini-stats { grid-template-columns: repeat(2, 1fr); }
        .demande { grid-template-columns: 1fr; gap: 12px; }
        .btn-traiter { justify-content: center; }
        .resa-ligne { grid-template-columns: 80px minmax(0, 1fr); }
        .resa-qui, .resa-ligne .btn-voir { grid-column: 1 / -1; }
    }
    @media (max-width: 600px) {
        .banner { padding: 18px; flex-direction: column; align-items: flex-start; }
        .banner .texte h2 { font-size: 17px; }
        .banner .texte p { font-size: 12.5px; }
        .banner .date-badge {
            align-self: stretch; display: flex; align-items: center;
            justify-content: space-between; padding: 8px 14px; text-align: left;
        }
        .banner .date-badge .jour { font-size: 15px; display: inline; }
        .stats { grid-template-columns: 1fr; gap: 12px; }
        .stat-card { padding: 14px; }
        .stat-card .number { font-size: 19px; }
        .mini-stats, .mini-stats.deux { grid-template-columns: 1fr; gap: 12px; }
        .mini-card { padding: 13px; }
        .acces { width: 100%; justify-content: center; }
        .btn-voir { width: 100%; justify-content: center; }
        .demande { padding: 14px 16px; }
        .resa-ligne { padding: 14px 16px; }
        .pied-liste { padding: 12px 16px; }
        .carte-blanche { padding: 16px; }
    }
</style>

<div class="banner">
    <div class="texte">
        <h2>Bonjour, {{ auth()->user()->name }}</h2>
        <p>
            @if ($aTraiter->count() > 0)
                {{ $aTraiter->count() }} demande{{ $aTraiter->count() > 1 ? 's' : '' }} en attente de votre validation.
            @else
                Aucune demande en attente. Voici un aperçu de l'activité des salles de votre direction.
            @endif
        </p>
    </div>
    <div class="date-badge">
        <span class="jour">{{ \Carbon\Carbon::now()->translatedFormat('d M') }}</span>
        {{ \Carbon\Carbon::now()->translatedFormat('Y') }}
    </div>
</div>

{{-- ===== ACCÈS RAPIDES ===== --}}
<nav class="acces-rapides" aria-label="Accès rapides">
    <a href="{{ route('admin.salles.create') }}" class="acces">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter une salle
    </a>
    <a href="{{ route('salles.liste') }}" class="acces">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg> Réserver une salle
    </a>
    <a href="{{ route('reservations.mes_demandes') }}" class="acces">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg> Mes demandes
    </a>
    <a href="{{ route('planning.index') }}" class="acces">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg> Planning
    </a>
    <a href="{{ route('validation.export') }}" class="acces">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg> Exporter en Excel
    </a>
</nav>

<div class="stats">
    <a href="{{ route('validation.index') }}" class="stat-card {{ $stats['en_cours'] > 0 ? 'attention' : '' }}" aria-label="Demandes en attente : {{ $stats['en_cours'] }}">
        <div class="stat-icone ton-or"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
        <div><div class="number">{{ $stats['en_cours'] }}</div><div class="label">Demandes en attente</div></div>
    </a>
    <a href="{{ route('validation.index') }}" class="stat-card" aria-label="Demandes confirmées : {{ $stats['validees'] }}">
        <div class="stat-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></div>
        <div><div class="number">{{ $stats['validees'] }}</div><div class="label">Demandes confirmées</div></div>
    </a>
    <a href="{{ route('validation.index') }}" class="stat-card" aria-label="Demandes refusées : {{ $stats['rejetees'] }}">
        <div class="stat-icone ton-rouge"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></div>
        <div><div class="number">{{ $stats['rejetees'] }}</div><div class="label">Demandes refusées</div></div>
    </a>
    <a href="{{ route('admin.salles.index') }}" class="stat-card" aria-label="Salles au total : {{ $stats['total_salles'] }}">
        <div class="stat-icone ton-gris"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></div>
        <div><div class="number">{{ $stats['total_salles'] }}</div><div class="label">Salles au total</div></div>
    </a>
</div>

{{-- Répartition des demandes --}}
<div class="carte-blanche">
    <div class="barre-repartition" role="img"
         aria-label="Répartition des demandes : {{ $stats['en_cours'] }} en attente, {{ $stats['validees'] }} confirmées, {{ $stats['rejetees'] }} refusées">
        <span class="seg-attente" style="width: {{ $pctAttente }}%"></span>
        <span class="seg-validee" style="width: {{ $pctValidees }}%"></span>
        <span class="seg-refusee" style="width: {{ $pctRejetees }}%"></span>
    </div>
    <div class="legende">
        <span><i class="seg-attente"></i> En attente ({{ $stats['en_cours'] }})</span>
        <span><i class="seg-validee"></i> Confirmées ({{ $stats['validees'] }})</span>
        <span><i class="seg-refusee"></i> Refusées ({{ $stats['rejetees'] }})</span>
    </div>
</div>

{{-- ===== DEMANDES À TRAITER ===== --}}
<div class="section-titre">
    Demandes à traiter
    @if ($aTraiter->count() > 0)
        <span class="compteur">{{ $aTraiter->count() }}</span>
    @endif
</div>

<div class="liste-demandes">
    @forelse ($aTraiterAffichees as $d)
        @php
            $demandeur = $d->utilisateur->name ?? 'Utilisateur';
            $initiale = mb_strtoupper(mb_substr($demandeur, 0, 1));
            $debut = \Carbon\Carbon::parse($d->date_reserv);
            $fin = $d->date_fin ? \Carbon\Carbon::parse($d->date_fin) : $debut;
            $periode = $debut->isSameDay($fin)
                ? $debut->format('d/m/Y')
                : $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
            $horaire = ($d->heure_debut && $d->heure_fin)
                ? substr($d->heure_debut, 0, 5) . ' - ' . substr($d->heure_fin, 0, 5)
                : 'Journée entière';

            // Urgence : la période est déjà passée, ou commence dans 2 jours au plus
            $depassee = $fin->lt($aujourdhui);
            $urgente = ! $depassee && $debut->lte($aujourdhui->copy()->addDays(2));

            // La salle est-elle déjà confirmée sur ce créneau ?
            $conflit = false;
            try {
                if ($d->salle && ! $depassee) {
                    $conflit = $d->salle->reservations()
                        ->where('statut', 'confirmee')
                        ->where('id', '!=', $d->id)
                        ->chevauche(
                            $debut->format('Y-m-d'),
                            $fin->format('Y-m-d'),
                            $d->heure_debut ? substr($d->heure_debut, 0, 5) : null,
                            $d->heure_fin ? substr($d->heure_fin, 0, 5) : null
                        )
                        ->exists();
                }
            } catch (\Throwable $e) {
                $conflit = false;
            }
        @endphp

        <div class="demande {{ $depassee ? 'depassee' : '' }}">
            <div class="demande-qui">
                <div class="avatar">{{ $initiale }}</div>
                <div style="min-width:0;">
                    <div class="demande-nom">{{ $demandeur }}</div>
                    <div class="demande-sous">
                        {{ $d->service ?: ($d->direction ?: 'Demandeur') }} &middot; {{ $d->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            <div class="demande-detail">
                <div class="demande-salle">
                    {{ $d->salle->nom ?? 'Salle supprimée' }}
                    <span class="tag-motif">{{ ucfirst(mb_strtolower($d->motif)) }}</span>
                    @if ($urgente)
                        <span class="badge badge-urgent">Urgent</span>
                    @elseif ($depassee)
                        <span class="badge badge-depasse">Date dépassée</span>
                    @endif
                </div>
                <div class="demande-infos">
                    <span><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg> {{ $periode }}</span>
                    <span><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ $horaire }}</span>
                    <span><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/></svg> {{ $d->nombre }} participant{{ $d->nombre > 1 ? 's' : '' }}</span>
                </div>
                @if ($conflit)
                    <div class="alerte-conflit">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                        Créneau déjà confirmé pour cette salle
                    </div>
                @endif
            </div>

            <a href="{{ route('validation.index') }}" class="btn-traiter" aria-label="Traiter la demande de {{ $demandeur }}">
                Traiter <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    @empty
        <div class="vide-demandes">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l3 3 5-6"/></svg>
            <h3>Tout est à jour</h3>
            <p>Aucune demande en attente de validation.</p>
        </div>
    @endforelse

    @if ($aTraiter->count() > $aTraiterAffichees->count())
        <div class="pied-liste">
            <span>{{ $aTraiter->count() - $aTraiterAffichees->count() }} autre(s) demande(s) en attente</span>
            <a href="{{ route('validation.index') }}" class="btn-voir">Voir toutes les demandes <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
    @endif
</div>


{{-- ===== ÉTAT DES SALLES ===== --}}
<div class="section-titre">État des salles</div>
<div class="mini-stats">
    <div class="mini-card">
        <div class="mini-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></div>
        <div><div class="valeur">{{ $statsSalles['disponibles'] }}</div><div class="libelle">Disponibles</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-rouge"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg></div>
        <div><div class="valeur">{{ $statsSalles['indisponibles'] }}</div><div class="libelle">Indisponibles</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-or"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.2-.5-.5-2.2 2.2-2.8z"/></svg></div>
        <div><div class="valeur">{{ $statsSalles['maintenance'] }}</div><div class="libelle">En maintenance</div></div>
    </div>
</div>

{{-- ===== AUJOURD'HUI ===== --}}
<div class="section-titre">
    Aujourd'hui
    @if ($reservationsAujourdhui->count() > 0)
        <span class="compteur">{{ $reservationsAujourdhui->count() }}</span>
    @endif
</div>

<div class="carte-blanche" style="margin-top:0;">
    <div class="occupation-tete">
        <strong>{{ $pctOccupation }} %</strong>
        <span>{{ $sallesOccupees }} salle{{ $sallesOccupees > 1 ? 's' : '' }} occupée{{ $sallesOccupees > 1 ? 's' : '' }} sur {{ $stats['total_salles'] }}</span>
    </div>
    <div class="jauge" role="img" aria-label="Taux d'occupation des salles aujourd'hui : {{ $pctOccupation }} pour cent">
        <div style="width: {{ $pctOccupation }}%"></div>
    </div>
</div>

<div class="resume-activite">
    @forelse ($reservationsAujourdhui as $r)
        @php
            $heureResa = ($r->heure_debut && $r->heure_fin)
                ? substr($r->heure_debut, 0, 5) . ' - ' . substr($r->heure_fin, 0, 5)
                : 'Journée';
        @endphp
        <div class="resa-ligne">
            <div class="resa-heure">{{ $heureResa }}</div>
            <div>
                <div class="resa-salle">{{ $r->salle->nom ?? 'Salle supprimée' }}</div>
                <div class="resa-sous">{{ ucfirst(mb_strtolower($r->motif)) }}</div>
            </div>
            <div class="resa-qui">{{ $r->utilisateur->name ?? 'Utilisateur' }}</div>
            <a href="{{ route('reservations.attestation', $r) }}" class="btn-voir" target="_blank" rel="noopener"
               aria-label="Attestation PDF de la réservation de {{ $r->salle->nom ?? 'la salle' }}">
                Attestation PDF
            </a>
        </div>
    @empty
        <div class="vide-demandes">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg>
            <h3>Aucune salle occupée aujourd'hui</h3>
            <p>Les réservations confirmées du jour apparaîtront ici.</p>
        </div>
    @endforelse

    <div class="pied-liste">
        <span>{{ $demandesEnAttente->count() }} demande(s) en attente de validation</span>
        <a href="{{ route('validation.index') }}" class="btn-voir">Voir les réservations <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>
</div>
@endsection