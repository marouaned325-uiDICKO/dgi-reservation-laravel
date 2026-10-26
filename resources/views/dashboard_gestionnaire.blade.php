@extends('layouts.dgi')

@section('title', 'Tableau de bord - DGI')

@section('content')
@php
    // Demandes en attente : les plus anciennes d'abord (8 maximum sur le tableau de bord)
    $aTraiter = $demandesEnAttente->sortBy('created_at')->values();
    $aTraiterAffichees = $aTraiter->take(8);
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

    /* ===== DEMANDES À TRAITER ===== */
    .liste-demandes { background: white; border-radius: 12px; border: 1px solid #eef1ef; overflow: hidden; }
    .demande {
        display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1.6fr) auto;
        gap: 16px; align-items: center; padding: 16px 22px; border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s ease;
    }
    .demande:last-child { border-bottom: none; }
    .demande:hover { background: #fafcfb; }

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

    .resume-activite { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #eef1ef; }
    .resume-ligne {
        display: flex; justify-content: space-between; align-items: center;
        padding: 16px 22px; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 10px;
    }
    .resume-ligne:last-child { border-bottom: none; }
    .resume-gauche { display: flex; align-items: center; gap: 14px; }
    .resume-icone {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .resume-texte { font-size: 13.5px; color: #444; }
    .resume-texte strong { color: #1A8969; font-size: 18px; margin-right: 4px; }

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
        .resume-ligne { padding: 14px 16px; }
        .btn-voir { width: 100%; justify-content: center; }
        .demande { padding: 14px 16px; }
        .pied-liste { padding: 12px 16px; }
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

<div class="stats">
    <a href="{{ route('validation.index') }}" class="stat-card {{ $stats['en_cours'] > 0 ? 'attention' : '' }}">
        <div class="stat-icone ton-or"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
        <div><div class="number">{{ $stats['en_cours'] }}</div><div class="label">Demandes en attente</div></div>
    </a>
    <div class="stat-card">
        <div class="stat-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></div>
        <div><div class="number">{{ $stats['validees'] }}</div><div class="label">Demandes confirmées</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icone ton-rouge"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></div>
        <div><div class="number">{{ $stats['rejetees'] }}</div><div class="label">Demandes refusées</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icone ton-gris"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></div>
        <div><div class="number">{{ $stats['total_salles'] }}</div><div class="label">Salles au total</div></div>
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

            // La salle est-elle déjà confirmée sur ce créneau ?
            $conflit = false;
            try {
                if ($d->salle) {
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

        <div class="demande">
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

            <a href="{{ route('validation.index') }}" class="btn-traiter">
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

<div class="section-titre">Répartition par motif</div>
<div class="mini-stats deux">
    <div class="mini-card">
        <div class="mini-icone ton-bleu"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/></svg></div>
        <div><div class="valeur">{{ $statsMotif['conference'] }}</div><div class="libelle">Conférences</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.8"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/></svg></div>
        <div><div class="valeur">{{ $statsMotif['reunion'] }}</div><div class="libelle">Réunions</div></div>
    </div>
</div>

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

<div class="section-titre">Activité</div>
<div class="resume-activite">
    <div class="resume-ligne">
        <div class="resume-gauche">
            <div class="resume-icone ton-or"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
            <div class="resume-texte"><strong>{{ $demandesEnAttente->count() }}</strong> demande(s) en attente de validation</div>
        </div>
        <a href="{{ route('validation.index') }}" class="btn-voir">Voir les demandes <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>
    <div class="resume-ligne">
        <div class="resume-gauche">
            <div class="resume-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg></div>
            <div class="resume-texte"><strong>{{ $reservationsAujourdhui->count() }}</strong> salle(s) occupée(s) aujourd'hui</div>
        </div>
        <a href="{{ route('validation.index') }}" class="btn-voir">Voir les réservations <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>
</div>
@endsection