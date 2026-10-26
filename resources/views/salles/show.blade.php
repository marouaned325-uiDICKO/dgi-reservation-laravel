@extends('layouts.public')

@section('title', $salle->nom . ' - DGI')

@section('content')

@php
    $equipements = collect(preg_split('/[,;\n]+/', (string) $salle->equipement))
        ->map(fn ($e) => trim($e))
        ->filter()
        ->values();

    // Réservations confirmées en cours ou à venir
    $occupations = $salle->reservations()
        ->where('statut', 'confirmee')
        ->whereRaw('COALESCE(date_fin, date_reserv) >= ?', [now()->toDateString()])
        ->orderBy('date_reserv')
        ->get();

    $libellePeriode = function ($r) {
        $debut = \Carbon\Carbon::parse($r->date_reserv);
        $fin = $r->date_fin ? \Carbon\Carbon::parse($r->date_fin) : $debut;
        return $debut->isSameDay($fin)
            ? 'Le ' . $debut->format('d/m/Y')
            : 'Du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
    };

    $libelleHeures = fn ($r) => ($r->heure_debut && $r->heure_fin)
        ? substr($r->heure_debut, 0, 5) . ' - ' . substr($r->heure_fin, 0, 5)
        : 'Journée entière';

    $libellesStatut = [
        'disponible'   => 'Disponible',
        'indisponible' => 'Indisponible',
        'maintenance'  => 'En maintenance',
    ];

    $utilisateur = auth()->user();
    $estAgent = $utilisateur && $utilisateur->role === 'agent';
    $reservable = $salle->statut === 'disponible';

    $lienRetour = ($estAgent && \Illuminate\Support\Facades\Route::has('salles.liste'))
        ? route('salles.liste')
        : route('salles.galerie');
@endphp

<style>
    .svg { width: 18px; height: 18px; flex-shrink: 0; }

    .detail-container { max-width: 960px; margin: 0 auto; padding: 24px 20px 40px; }

    .retour-lien {
        display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px;
        color: #1A8969; background: white; text-decoration: none;
        font-size: 13.5px; font-weight: 700; padding: 9px 18px;
        border-radius: 24px; border: 1.5px solid #1A8969; transition: .2s;
    }
    .retour-lien:hover { background: #1A8969; color: white; }

    .detail-grille { display: grid; grid-template-columns: 1.1fr 1fr; gap: 22px; align-items: start; }

    .carte { background: white; border-radius: 14px; border: 1px solid #eef1ef; box-shadow: 0 2px 10px rgba(0,0,0,.05); overflow: hidden; }

    .photo-salle { width: 100%; height: 320px; object-fit: cover; display: block; background: #eef1ef; }

    .carte-corps { padding: 22px 24px; }

    .titre-ligne { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 4px; }
    .titre-ligne h1 { font-size: 22px; color: #1A8969; margin: 0; }
    .reference { font-size: 12.5px; color: #999; margin-bottom: 18px; }

    .badge-statut { display: inline-flex; align-items: center; gap: 6px; padding: 5px 13px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .badge-statut::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .statut-disponible   { background: #e6f5ef; color: #1A8969; }
    .statut-indisponible { background: #fdeaea; color: #c0392b; }
    .statut-maintenance  { background: #fff4e0; color: #b9770e; }

    .infos { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
    .info { display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: #f7faf9; border: 1px solid #eef1ef; border-radius: 10px; }
    .info .icone { width: 36px; height: 36px; border-radius: 9px; background: #e6f5ef; color: #1A8969; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .info small { display: block; font-size: 11.5px; color: #888; margin-bottom: 1px; }
    .info strong { font-size: 14px; color: #333; }
    .info.large { grid-column: 1 / -1; }

    .section-titre { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #333; margin: 0 0 10px; }
    .section-titre .svg { color: #1A8969; }

    .puces { display: flex; flex-wrap: wrap; gap: 8px; }
    .puce { padding: 6px 13px; background: #e6f5ef; color: #146e54; border-radius: 20px; font-size: 12.5px; font-weight: 600; }
    .vide { font-size: 13px; color: #999; }

    .liste-occ { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .occ { padding: 10px 14px; border-radius: 10px; background: #fff4e0; border-left: 4px solid #e0a030; }
    .occ strong { display: block; font-size: 13px; color: #7a5200; }
    .occ span { font-size: 12px; color: #9a7a30; }
    .libre { padding: 10px 14px; border-radius: 10px; background: #e6f5ef; color: #146e54; font-size: 13px; font-weight: 600; border-left: 4px solid #1A8969; }

    .bloc { margin-bottom: 22px; }

    .btn-reserver {
        display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
        padding: 13px; background: #1A8969; color: white; text-decoration: none;
        border-radius: 24px; font-size: 14px; font-weight: 700; transition: .2s;
    }
    .btn-reserver:hover { background: #146e54; }
    .btn-reserver.desactive { background: #ccc; cursor: not-allowed; pointer-events: none; }
    .note { margin-top: 8px; font-size: 12px; color: #888; text-align: center; }

    @media (max-width: 800px) {
        .detail-grille { grid-template-columns: 1fr; }
        .photo-salle { height: 240px; }
    }
    @media (max-width: 480px) {
        .infos { grid-template-columns: 1fr; }
        .detail-container { padding: 16px 12px 30px; }
    }
</style>

<div class="detail-container">

    <a href="{{ $lienRetour }}" class="retour-lien">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Retour aux salles
    </a>

    <div class="detail-grille">

        {{-- Colonne gauche : photo + description --}}
        <div class="carte">
            <img class="photo-salle"
                 src="{{ $salle->photo ? asset('storage/' . $salle->photo) : asset('images/salles/placeholder.jpg') }}"
                 alt="{{ $salle->nom }}">
            <div class="carte-corps">
                <div class="bloc" style="margin-bottom:0">
                    <h3 class="section-titre">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        Équipements
                    </h3>
                    @if ($equipements->isNotEmpty())
                        <div class="puces">
                            @foreach ($equipements as $equipement)
                                <span class="puce">{{ $equipement }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="vide">Aucun équipement renseigné.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne droite : informations --}}
        <div class="carte">
            <div class="carte-corps">

                <div class="titre-ligne">
                    <h1>{{ $salle->nom }}</h1>
                    <span class="badge-statut statut-{{ $salle->statut }}">{{ $libellesStatut[$salle->statut] ?? ucfirst($salle->statut) }}</span>
                </div>
                <div class="reference">Réf. {{ $salle->reference ?? 'Salle-' . now()->format('Y') . '-' . str_pad($salle->id, 4, '0', STR_PAD_LEFT) }}</div>

                <div class="infos">
                    <div class="info large">
                        <div class="icone">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
                        </div>
                        <div>
                            <small>Direction</small>
                            <strong>{{ $salle->direction->nom_direction ?? 'Non renseignée' }}</strong>
                        </div>
                    </div>
                    <div class="info">
                        <div class="icone">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <small>Ville</small>
                            <strong>{{ $salle->localite ?? '—' }}</strong>
                        </div>
                    </div>
                    <div class="info">
                        <div class="icone">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <small>Capacité</small>
                            <strong>{{ $salle->capacite ? $salle->capacite . ' places' : '—' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="bloc">
                    <h3 class="section-titre">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Occupation
                    </h3>
                    @if ($occupations->isEmpty())
                        <div class="libre">Aucune réservation confirmée à venir : la salle est libre.</div>
                    @else
                        <ul class="liste-occ">
                            @foreach ($occupations as $occ)
                                <li class="occ">
                                    <strong>{{ $libellePeriode($occ) }}</strong>
                                    <span>{{ $libelleHeures($occ) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if ($estAgent)
                    @if ($reservable)
                        <a href="{{ route('reservations.create', ['salle' => $salle->id]) }}" class="btn-reserver">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Réserver cette salle
                        </a>
                    @else
                        <span class="btn-reserver desactive">Salle non réservable</span>
                        <p class="note">Cette salle est actuellement {{ strtolower($libellesStatut[$salle->statut] ?? $salle->statut) }}.</p>
                    @endif
                @elseif (!$utilisateur)
                    <a href="{{ route('login') }}" class="btn-reserver">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Se connecter pour réserver
                    </a>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection