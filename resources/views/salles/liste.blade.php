@extends('layouts.dgi')

@section('title', 'Salles disponibles - DGI')

@section('content')

@php
    $maintenant = now();

    // Réservations confirmées NON terminées (celles dont l'heure de fin est passée disparaissent)
    $occupations = \App\Models\Reservation::where('statut', 'confirmee')
        ->whereRaw('COALESCE(date_fin, date_reserv) >= ?', [$maintenant->toDateString()])
        ->orderBy('date_reserv')
        ->orderBy('heure_debut')
        ->get()
        ->filter(function ($r) use ($maintenant) {
            $jourFin = \Carbon\Carbon::parse($r->date_fin ?? $r->date_reserv)->toDateString();
            $fin = $r->heure_fin
                ? \Carbon\Carbon::parse($jourFin . ' ' . substr($r->heure_fin, 0, 5))
                : \Carbon\Carbon::parse($jourFin)->endOfDay();

            return $fin->gt($maintenant);
        })
        ->groupBy('salle_id');

    // La réservation occupe-t-elle la salle à cet instant ?
    $estEnCours = function ($r) use ($maintenant) {
        $jour = $maintenant->toDateString();
        $debutJour = \Carbon\Carbon::parse($r->date_reserv)->toDateString();
        $finJour = \Carbon\Carbon::parse($r->date_fin ?? $r->date_reserv)->toDateString();

        if ($jour < $debutJour || $jour > $finJour) {
            return false;
        }
        if (!$r->heure_debut || !$r->heure_fin) {
            return true; // sans heures : journée entière
        }

        $h = $maintenant->format('H:i');

        return substr($r->heure_debut, 0, 5) <= $h && $h < substr($r->heure_fin, 0, 5);
    };

    $libellePeriode = function ($r) {
        $debut = \Carbon\Carbon::parse($r->date_reserv);
        $fin = $r->date_fin ? \Carbon\Carbon::parse($r->date_fin) : $debut;
        return $debut->isSameDay($fin)
            ? 'le ' . $debut->format('d/m/Y')
            : 'du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
    };

    $libelleHeures = fn ($r) => ($r->heure_debut && $r->heure_fin)
        ? substr($r->heure_debut, 0, 5) . ' - ' . substr($r->heure_fin, 0, 5)
        : 'Journée entière';
@endphp

<style>
    .svg { width: 16px; height: 16px; flex-shrink: 0; }

    .galerie-container { padding: 0; background: transparent; }

    .entete-page { margin-bottom: 18px; }
    .entete-page h2 { color: #1A8969; font-size: 21px; margin-bottom: 4px; }
    .entete-page p { color: #888; font-size: 13px; }

    .barre-recherche {
        display: flex; align-items: center; justify-content: space-between;
        gap: 14px; flex-wrap: wrap; margin-bottom: 18px;
    }
    .recherche-salle {
        background: white; border: 1px solid #e0e0e0; border-radius: 12px;
        padding: 11px 16px; display: flex; align-items: center; gap: 10px;
        flex: 1; max-width: 460px; min-width: 240px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: 0.2s ease;
    }
    .recherche-salle:focus-within { border-color: #1A8969; box-shadow: 0 0 0 3px rgba(26,137,105,0.12); }
    .recherche-salle .svg { color: #1A8969; width: 18px; height: 18px; }
    .recherche-salle input {
        width: 100%; border: none; outline: none; font-size: 14px;
        background: transparent; font-family: inherit; color: #333;
    }
    .resultats-info { font-size: 13px; color: #888; }
    .resultats-info strong { color: #1A8969; }

    .galerie-salles {
        display: grid; grid-template-columns: repeat(5, 1fr);
        gap: 18px; align-items: stretch; transition: filter 0.25s ease;
    }
    .galerie-salles.floutee { pointer-events: none; user-select: none; }

    .salle-card {
        width: 100%; height: 100%; display: flex; flex-direction: column;
        background: white; border-radius: 14px; overflow: hidden;
        border: 1px solid #eef1ef; transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .salle-card:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(0,0,0,0.1); }
    .salle-image-wrapper { position: relative; overflow: hidden; }
    .salle-image { width: 100%; height: 150px; object-fit: cover; display: block; transition: transform 0.35s ease; }
    .salle-card:hover .salle-image { transform: scale(1.04); }
    .salle-capacite {
        position: absolute; top: 10px; right: 10px;
        display: inline-flex; align-items: center; gap: 5px;
        background: rgba(255,255,255,0.95); color: #1A8969;
        font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .salle-capacite .svg { width: 13px; height: 13px; }

    .salle-titre { padding: 16px 16px 12px; border-bottom: 1px solid #f0f0f0; }
    .salle-titre h3 {
        margin: 0; font-size: 16px; font-weight: 700; color: #1A8969; min-height: 40px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .salle-info { padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; }
    .salle-localite-ligne {
        display: flex; align-items: center; gap: 6px; margin-top: 0;
        font-size: 12.5px; color: #777;
    }
    .salle-localite-ligne .svg { color: #8a9a92; width: 14px; height: 14px; }
    .salle-bas {
        display: flex; justify-content: space-between; align-items: center; gap: 8px;
        border-top: 1px solid #f0f0f0; padding-top: 12px;
    }

    .btn-details {
        display: inline-flex; align-items: center; gap: 6px; color: #1A8969; background: white;
        font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 20px;
        border: 1.3px solid #1A8969; transition: 0.2s ease; white-space: nowrap; cursor: pointer; font-family: inherit;
    }
    .btn-details .svg { width: 14px; height: 14px; }
    .btn-details:hover { background: #1A8969; color: white; }
    .btn-reserver {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        color: white; background: #1A8969; font-weight: 700; text-decoration: none;
        font-size: 12px; padding: 7px 16px; border-radius: 20px; border: 1.3px solid #1A8969;
        transition: 0.2s; white-space: nowrap; box-shadow: 0 2px 6px rgba(26,137,105,0.25);
    }
    .btn-reserver .svg { width: 14px; height: 14px; }
    .btn-reserver:hover { background: #146e54; border-color: #146e54; }

    .aucune-salle {
        grid-column: 1 / -1; text-align: center; padding: 56px 20px;
        background: white; border-radius: 14px; border: 1px dashed #d5ddd9;
    }
    .aucune-salle .svg { width: 38px; height: 38px; color: #c5cfca; margin-bottom: 10px; }
    .aucune-salle h3 { margin: 0 0 6px; color: #444; }
    .aucune-salle p { margin: 0; color: #999; font-size: 13px; }

    @media (min-width: 1200px) { .galerie-salles { grid-template-columns: repeat(5, 1fr); } }
    @media (max-width: 1199px) { .galerie-salles { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 950px)  { .galerie-salles { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 700px)  { .galerie-salles { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px)  {
        .galerie-salles { grid-template-columns: 1fr; }
        .salle-card { max-width: 360px; margin: 0 auto; }
        .recherche-salle { max-width: 100%; }
    }

    /* ===== MODALE DÉTAILS ===== */
    .modale-fond {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6); z-index: 1000;
        align-items: center; justify-content: center; padding: 20px; pointer-events: auto;
    }
    .modale-fond.ouverte { display: flex; }
    .modale-boite {
        background: white; border-radius: 16px; max-width: 500px; width: 100%;
        max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparitionModale 0.2s ease;
    }
    @keyframes apparitionModale { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .modale-image-zone { position: relative; }
    .modale-image { width: 100%; height: 220px; object-fit: cover; display: block; border-radius: 16px 16px 0 0; }
    .modale-fermer {
        position: absolute; top: 12px; right: 12px;
        background: rgba(255,255,255,0.95); border: none; width: 32px; height: 32px; border-radius: 50%;
        cursor: pointer; color: #555; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: 0.2s ease;
    }
    .modale-fermer:hover { background: #fff; color: #d9534f; }
    .modale-contenu { padding: 22px 26px 26px; }
    .modale-contenu h3 { margin: 0 0 12px; font-size: 20px; color: #1a1a1a; }
    .modale-infos { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 4px; }
    .modale-badge {
        display: inline-flex; align-items: center; gap: 7px;
        background: #e8f5e9; color: #1A8969; font-weight: 700;
        padding: 6px 14px; border-radius: 20px; font-size: 12.5px;
    }
    .modale-badge.neutre { background: #eef1ef; color: #555; font-weight: 600; }
    .modale-section-titre {
        font-size: 12px; font-weight: 700; color: #5b6b63;
        text-transform: uppercase; letter-spacing: 0.6px; margin: 20px 0 10px;
    }
    .modale-equipements { display: flex; flex-wrap: wrap; gap: 8px; }
    .equipement-chip {
        background: #f6f8f7; border: 1px solid #e6ece9; color: #444;
        padding: 5px 12px; border-radius: 8px; font-size: 12.5px;
    }
    .equipement-vide { font-size: 13.5px; color: #999; }
    .modale-boutons { display: flex; gap: 10px; margin-top: 22px; border-top: 1px solid #f0f0f0; padding-top: 18px; }
    .modale-boutons .btn-reserver { flex: 1; padding: 10px 16px; font-size: 13px; }
    .modale-boutons .btn-annuler {
        flex-shrink: 0; background: white; color: #777; border: 1.3px solid #ccc;
        padding: 9px 20px; border-radius: 20px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit;
    }
    .modale-boutons .btn-annuler:hover { background: #f5f5f5; border-color: #999; }

    .alerte {
        display: flex; align-items: center; gap: 12px; padding: 12px 16px;
        border-radius: 10px; margin-bottom: 16px; font-size: 13.5px; font-weight: 600;
    }
    .alerte .alerte-icone {
        width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0; color: white;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .alerte .alerte-icone .svg { width: 14px; height: 14px; }
    .alerte-succes { background: #e6f4ea; color: #1e6b3a; border: 1px solid #b7e0c4; }
    .alerte-succes .alerte-icone { background: #1A8969; }
    .alerte-erreur { background: #fdeaea; color: #a94442; border: 1px solid #f3c2c0; }
    .alerte-erreur .alerte-icone { background: #d9534f; }

    /* ===== CARTE CLIQUABLE + OCCUPATION ===== */
    .salle-card { cursor: pointer; }
    .salle-localite-ligne { flex-direction: column; align-items: flex-start; gap: 6px; }
    .salle-localite-ligne .ville { display: inline-flex; align-items: center; gap: 6px; }
    .occupation {
        display: inline-block; font-size: 11px; font-weight: 700; line-height: 1.3;
        padding: 3px 9px; border-radius: 12px; background: #fdeaea; color: #c0392b;
    }
    .occupation em { font-style: normal; opacity: 0.75; margin-left: 3px; }
    .occupation.libre { background: #e8f5e9; color: #1A8969; }

    .liste-occupations { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .liste-occupations li {
        display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
        background: #fff6f6; border: 1px solid #f6d6d4; border-radius: 8px; padding: 8px 12px; font-size: 12.5px;
    }
    .liste-occupations .occ-date { color: #c0392b; font-weight: 700; }
    .liste-occupations .occ-heure { color: #777; }
</style>

@if (session('success'))
<div class="alerte alerte-succes">
    <span class="alerte-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span>
    <span>{{ session('success') }}</span>
</div>
@endif

@if ($errors->any())
<div class="alerte alerte-erreur">
    <span class="alerte-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5h.01"/></svg></span>
    <span>{{ $errors->first() }}</span>
</div>
@endif

<div class="galerie-container">

    <div class="entete-page">
        <h2>Salles disponibles</h2>
        <p>Choisissez une salle pour déposer votre demande de réservation.</p>
    </div>

    <div class="barre-recherche">
        <div class="recherche-salle">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" id="rechercheSalle" placeholder="Rechercher une salle ou une ville...">
        </div>
        <div class="resultats-info" id="resultatsInfo">
            <strong>{{ $salles->count() }}</strong> salle{{ $salles->count() > 1 ? 's' : '' }} au total
        </div>
    </div>

    <div class="galerie-salles" id="galerieSalles">

        @forelse($salles as $salle)
            @php
                $urlImage = $salle->photo
                    ? asset('storage/' . $salle->photo)
                    : asset('images/salles/placeholder.jpg');
                $equipements = collect(preg_split('/[,;\n]+/', (string) ($salle->equipement ?? '')))
                    ->map(fn ($e) => trim($e))
                    ->filter()
                    ->values();
                $periodes = $occupations->get($salle->id, collect());
                $enCours = $periodes->first($estEnCours);
            @endphp

            <div class="salle-card" data-nom="{{ mb_strtolower($salle->nom . ' ' . ($salle->localite ?? '')) }}" onclick="ouvrirDetails({{ $salle->id }})">

                <div class="salle-titre">
                    <h3>{{ $salle->nom }}</h3>
                </div>

                <div class="salle-image-wrapper">
                    <img src="{{ $urlImage }}" alt="{{ $salle->nom }}" class="salle-image">
                    <span class="salle-capacite"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.8"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/></svg> {{ $salle->capacite }} places</span>
                </div>

                <div class="salle-info">
                    <div>
                        <div class="salle-localite-ligne">
                            <span class="ville"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg> {{ $salle->localite ?? '-' }}</span>
                            @if ($periodes->isNotEmpty())
                                @php $prochaine = $enCours ?? $periodes->first(); @endphp
                                <span class="occupation">
                                    Occupée
                                    @if ($enCours)
                                        @if ($enCours->heure_fin)
                                            jusqu'à {{ substr($enCours->heure_fin, 0, 5) }}
                                        @else
                                            jusqu'au {{ \Carbon\Carbon::parse($enCours->date_fin ?? $enCours->date_reserv)->format('d/m/Y') }}
                                        @endif
                                    @else
                                        le {{ \Carbon\Carbon::parse($prochaine->date_reserv)->format('d/m') }}
                                        @if ($prochaine->heure_debut)
                                            à {{ substr($prochaine->heure_debut, 0, 5) }}
                                        @endif
                                    @endif
                                </span>
                            @else
                                <span class="occupation libre">Libre</span>
                            @endif
                        </div>
                    </div>

                    <div class="salle-bas">
                        <button type="button" class="btn-details" onclick="event.stopPropagation(); ouvrirDetails({{ $salle->id }})">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg> Détails
                        </button>
                        <a href="{{ route('reservations.create', ['salle' => $salle->id]) }}" class="btn-reserver" onclick="event.stopPropagation()">
                            Réserver
                        </a>
                    </div>
                </div>
            </div>

            <!-- MODALE DÉTAILS -->
            <div class="modale-fond" id="modaleDetails{{ $salle->id }}">
                <div class="modale-boite">

                    <div class="modale-image-zone">
                        <img src="{{ $urlImage }}" alt="{{ $salle->nom }}" class="modale-image">
                        <button type="button" class="modale-fermer" onclick="fermerDetails({{ $salle->id }})" aria-label="Fermer"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
                    </div>

                    <div class="modale-contenu">
                        <h3>{{ $salle->nom }}</h3>

                        <div class="modale-infos">
                            <span class="modale-badge"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.8"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/></svg> {{ $salle->capacite }} places</span>
                            <span class="modale-badge neutre"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg> {{ $salle->localite ?? '-' }}</span>
                        </div>

                        <div class="modale-section-titre">Équipements</div>
                        @if ($equipements->isNotEmpty())
                            <div class="modale-equipements">
                                @foreach ($equipements as $equipement)
                                    <span class="equipement-chip">{{ $equipement }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="equipement-vide">Aucun équipement renseigné</div>
                        @endif

                        <div class="modale-section-titre">Réservations confirmées à venir</div>
                        @if ($periodes->isNotEmpty())
                            <ul class="liste-occupations">
                                @foreach ($periodes as $r)
                                    <li>
                                        <span class="occ-date">
                                            {{ $estEnCours($r) ? 'En cours' : 'Réservée' }} {{ $libellePeriode($r) }}
                                        </span>
                                        <span class="occ-heure">{{ $libelleHeures($r) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="equipement-vide">Aucune réservation confirmée à venir : la salle est libre.</div>
                        @endif

                        <div class="modale-boutons">
                            <button type="button" class="btn-annuler" onclick="fermerDetails({{ $salle->id }})">Fermer</button>
                            <a href="{{ route('reservations.create', ['salle' => $salle->id]) }}" class="btn-reserver">
                                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg> Réserver cette salle
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="aucune-salle">
                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 20v-6h6v6"/><path d="M7 8h2M11 8h2M15 8h2"/></svg>
                <h3>Aucune salle disponible</h3>
                <p>Les salles seront affichées ici.</p>
            </div>
        @endforelse

    </div>
</div>

<script>
    document.getElementById('rechercheSalle').addEventListener('input', function () {
        const recherche = this.value.toLowerCase().trim();
        let visibles = 0;

        document.querySelectorAll('.salle-card').forEach(function (card) {
            const ok = card.dataset.nom.includes(recherche);
            card.style.display = ok ? '' : 'none';
            if (ok) visibles++;
        });

        document.getElementById('resultatsInfo').innerHTML =
            '<strong>' + visibles + '</strong> salle' + (visibles > 1 ? 's' : '') +
            (recherche ? ' trouvée' + (visibles > 1 ? 's' : '') : ' au total');
    });

    function ouvrirDetails(id) {
        document.getElementById('modaleDetails' + id).classList.add('ouverte');
        document.getElementById('galerieSalles').classList.add('floutee');
        document.body.style.overflow = 'hidden';
    }

    function fermerDetails(id) {
        document.getElementById('modaleDetails' + id).classList.remove('ouverte');
        document.getElementById('galerieSalles').classList.remove('floutee');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.modale-fond').forEach(function (fond) {
        fond.addEventListener('click', function (e) {
            if (e.target === fond) fermerDetails(fond.id.replace('modaleDetails', ''));
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modale-fond.ouverte').forEach(function (fond) {
                fermerDetails(fond.id.replace('modaleDetails', ''));
            });
        }
    });

    // Met à jour l'état Libre / Occupée toutes les minutes (sans gêner la recherche ni la modale ouverte)
    setInterval(function () {
        const modaleOuverte = document.querySelector('.modale-fond.ouverte');
        const rechercheEnCours = document.getElementById('rechercheSalle').value.trim() !== '';
        if (!modaleOuverte && !rechercheEnCours) {
            window.location.reload();
        }
    }, 60000);
</script>

@endsection