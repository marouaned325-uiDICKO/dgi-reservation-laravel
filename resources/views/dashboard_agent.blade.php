@extends('layouts.dgi')

@section('title', 'Tableau de bord - DGI')

@section('content')
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
    .banner .texte, .banner .droite { position: relative; z-index: 1; }
    .banner .texte h2 { font-size: 21px; margin-bottom: 6px; font-weight: 700; }
    .banner .texte p { color: #d9f0e3; font-size: 14px; }
    .banner .droite { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .banner .date-badge {
        background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22);
        padding: 10px 20px; border-radius: 10px; text-align: center;
        font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; flex-shrink: 0;
    }
    .banner .date-badge .jour { font-size: 22px; font-weight: bold; display: block; letter-spacing: 0; }
    .banner .btn-reserver {
        display: inline-flex; align-items: center; gap: 8px;
        background: white; color: #1A8969; text-decoration: none;
        padding: 10px 20px; border-radius: 30px; font-size: 13px; font-weight: 700;
        white-space: nowrap; transition: 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .banner .btn-reserver .svg { width: 16px; height: 16px; }
    .banner .btn-reserver:hover { background: #f0f0f0; transform: translateY(-1px); }

    .section-titre {
        font-size: 13px; font-weight: 700; color: #5b6b63;
        text-transform: uppercase; letter-spacing: 0.7px;
        margin: 30px 0 14px; display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 8px;
    }
    .section-titre .texte-titre { display: flex; align-items: center; flex: 1; }
    .section-titre .texte-titre::before {
        content: ""; width: 4px; height: 16px; background: #D4AF37; border-radius: 2px; margin-right: 10px;
    }
    .btn-voir-tout {
        display: inline-flex; align-items: center; gap: 6px;
        background: white; color: #1A8969; border: 1.3px solid #1A8969;
        text-decoration: none; font-size: 12px; font-weight: 700;
        text-transform: none; letter-spacing: 0; padding: 6px 14px; border-radius: 20px; transition: 0.2s ease;
    }
    .btn-voir-tout .svg { width: 14px; height: 14px; }
    .btn-voir-tout:hover { background: #1A8969; color: white; }

    .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
    .stat-card {
        background: white; border-radius: 12px; padding: 16px 18px;
        display: flex; align-items: center; gap: 14px;
        border: 1px solid #eef1ef; transition: transform 0.2s ease, box-shadow 0.2s ease; min-width: 0;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.07); }
    .stat-icone {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .ton-vert  { background: #e8f5e9; color: #1A8969; }
    .ton-or    { background: #fff6dd; color: #b8860b; }
    .ton-rouge { background: #fdeaea; color: #d9534f; }
    .ton-gris  { background: #eef1ef; color: #5b6b63; }
    .stat-card .number { font-size: 22px; font-weight: 800; color: #1a1a1a; line-height: 1.1; }
    .stat-card .label { font-size: 12px; color: #777; margin-top: 3px; }

    .demandes-grille { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .demande {
        background: white; border-radius: 12px; padding: 16px 18px;
        border: 1px solid #eef1ef; border-left: 4px solid #ccc;
        transition: transform 0.2s ease, box-shadow 0.2s ease; min-width: 0;
        display: flex; flex-direction: column;
    }
    .demande:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.07); }
    .demande.confirmee  { border-left-color: #1A8969; }
    .demande.en_attente { border-left-color: #D4AF37; }
    .demande.refusee    { border-left-color: #d9534f; }
    .demande.annulee    { border-left-color: #999; }

    .demande-entete { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; }
    .demande-icone {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .demande.confirmee  .demande-icone { background: #e8f5e9; color: #1A8969; }
    .demande.en_attente .demande-icone { background: #fdf3d9; color: #b8860b; }
    .demande.refusee    .demande-icone { background: #fdeaea; color: #d9534f; }
    .demande.annulee    .demande-icone { background: #f0f0f0; color: #777; }
    .demande-titre { font-size: 14px; font-weight: 700; color: #1a1a1a; word-break: break-word; }
    .demande-ref { font-size: 11.5px; color: #999; margin-bottom: 12px; letter-spacing: 0.3px; }

    .demande-infos { display: flex; flex-direction: column; gap: 7px; }
    .demande-info { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: #555; word-break: break-word; }
    .demande-info .svg { width: 15px; height: 15px; color: #8a9a92; margin-top: 1px; }

    .demande-refus {
        font-size: 12px; color: #c0392b; background: #fdeaea;
        padding: 8px 10px; border-radius: 8px; margin-top: 10px; word-break: break-word;
    }
    .demande-bas {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: auto; padding-top: 12px; border-top: 1px solid #f0f0f0;
        flex-wrap: wrap; gap: 6px;
    }
    .demande-infos + .demande-bas, .demande-refus + .demande-bas { margin-top: 14px; }
    .badge {
        padding: 4px 12px 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
        white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;
    }
    .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .badge-confirmee  { background: #e6f4ea; color: #1e7a3c; }
    .badge-en_attente { background: #fff3cd; color: #8a6d0b; }
    .badge-refusee    { background: #fdeaea; color: #c0392b; }
    .badge-annulee    { background: #eee; color: #666; }
    .demande-depose { font-size: 11.5px; color: #999; }

    .vide {
        background: white; border-radius: 12px; padding: 44px 20px;
        text-align: center; color: #888; grid-column: 1 / -1; border: 1px solid #eef1ef;
    }
    .vide .svg { width: 38px; height: 38px; color: #c5cfca; margin-bottom: 10px; }
    .vide a { color: #1A8969; font-weight: 700; text-decoration: none; }
    .vide a:hover { text-decoration: underline; }

    @media (max-width: 900px) {
        .demandes-grille { grid-template-columns: repeat(2, 1fr); }
        .stats { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .demandes-grille { grid-template-columns: 1fr; }
        .stats { grid-template-columns: 1fr; gap: 12px; }
        .banner { padding: 18px; flex-direction: column; align-items: flex-start; }
        .banner .texte h2 { font-size: 17px; }
        .banner .texte p { font-size: 12.5px; }
        .banner .droite { width: 100%; flex-direction: column; align-items: stretch; }
        .banner .date-badge {
            align-self: stretch; display: flex; align-items: center;
            justify-content: space-between; padding: 8px 14px; text-align: left;
        }
        .banner .date-badge .jour { font-size: 15px; display: inline; }
        .banner .btn-reserver { justify-content: center; }
        .stat-card { padding: 14px; }
        .stat-card .number { font-size: 19px; }
        .demande { padding: 14px; }
    }
</style>

@php
    $etats = [
        'en_attente' => 'En attente',
        'confirmee'  => 'Confirmée',
        'refusee'    => 'Refusée',
        'annulee'    => 'Annulée',
    ];
@endphp

<div class="banner">
    <div class="texte">
        <h2>Bonjour, {{ auth()->user()->name }}</h2>
        <p>Suivez ici l'état de vos demandes de réservation.</p>
    </div>
    <div class="droite">
        <a href="{{ route('salles.liste') }}" class="btn-reserver">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Nouvelle réservation
        </a>
        <div class="date-badge">
            <span class="jour">{{ \Carbon\Carbon::now()->translatedFormat('d M') }}</span>
            {{ \Carbon\Carbon::now()->translatedFormat('Y') }}
        </div>
    </div>
</div>

<div class="stats">
    <div class="stat-card">
        <div class="stat-icone ton-gris"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></div>
        <div>
            <div class="number">{{ $stats['en_cours'] + $stats['validees'] + $stats['rejetees'] }}</div>
            <div class="label">Total des demandes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icone ton-or"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
        <div>
            <div class="number">{{ $stats['en_cours'] }}</div>
            <div class="label">En attente</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icone ton-vert"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></div>
        <div>
            <div class="number">{{ $stats['validees'] }}</div>
            <div class="label">Confirmées</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icone ton-rouge"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></div>
        <div>
            <div class="number">{{ $stats['rejetees'] }}</div>
            <div class="label">Refusées</div>
        </div>
    </div>
</div>

<div class="section-titre">
    <span class="texte-titre">Mes dernières demandes</span>
    <a href="{{ route('reservations.mes_demandes') }}" class="btn-voir-tout">Voir mes demandes <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
</div>

<div class="demandes-grille">
    @forelse ($dernieresDemandes as $r)
    @php
        $statut = array_key_exists($r->statut, $etats) ? $r->statut : 'en_attente';
        $horaire = $r->heure_debut && $r->heure_fin
            ? substr($r->heure_debut, 0, 5) . ' - ' . substr($r->heure_fin, 0, 5)
            : ($r->heure_debut ? 'À partir de ' . substr($r->heure_debut, 0, 5)
            : ($r->heure_fin ? 'Jusqu\'à ' . substr($r->heure_fin, 0, 5) : 'Journée entière'));
        $debut = \Carbon\Carbon::parse($r->date_reserv);
        $fin = $r->date_fin ? \Carbon\Carbon::parse($r->date_fin) : $debut;
        $periode = $debut->isSameDay($fin)
            ? $debut->format('d/m/Y')
            : $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
    @endphp
    <div class="demande {{ $statut }}">
        <div class="demande-entete">
            <div class="demande-icone">
                @if ($statut === 'confirmee')
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                @elseif ($statut === 'refusee')
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                @elseif ($statut === 'annulee')
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg>
                @else
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                @endif
            </div>
            <div class="demande-titre">{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($r->motif)) }}</div>
        </div>
        <div class="demande-ref">{{ $r->reference }}</div>

        <div class="demande-infos">
            <div class="demande-info"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg> <span>{{ $r->salle->nom ?? 'Salle supprimée' }}</span></div>
            <div class="demande-info"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg> <span>{{ $r->salle->localite ?? '-' }}</span></div>
            <div class="demande-info"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg> <span>{{ $periode }}</span></div>
            <div class="demande-info"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> <span>{{ $horaire }}</span></div>
        </div>

        @if ($statut === 'refusee' && $r->motif_refus)
            <div class="demande-refus">{{ $r->motif_refus }}</div>
        @endif

        <div class="demande-bas">
            <span class="demande-depose">Déposée le {{ $r->created_at->format('d/m/Y') }}</span>
            <span class="badge badge-{{ $statut }}">{{ $etats[$statut] }}</span>
        </div>
    </div>
    @empty
    <div class="vide">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13L22 12v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6l3.5-7z"/></svg>
        <p>Vous n'avez encore fait aucune demande.</p>
        <p style="margin-top: 8px;"><a href="{{ route('salles.liste') }}">Voir les salles disponibles</a></p>
    </div>
    @endforelse
</div>
@endsection