@extends('layouts.dgi')

@section('title', 'Mes demandes - DGI')

@section('content')
<style>
    .svg { width: 16px; height: 16px; flex-shrink: 0; }

    .page-header {
        display: flex; justify-content: space-between; align-items: flex-end;
        margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
    }
    .page-header h2 { color: #1A8969; font-size: 21px; margin-bottom: 4px; }
    .page-header .sous-titre { color: #888; font-size: 13px; }

    .entete-droite { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

    .btn-nouvelle {
        display: inline-flex; align-items: center; gap: 7px;
        background: #1A8969; color: white; text-decoration: none;
        padding: 9px 18px; border-radius: 20px; font-size: 12.5px; font-weight: 700;
        box-shadow: 0 2px 8px rgba(26, 137, 105, 0.3); transition: 0.2s ease;
    }
    .btn-nouvelle:hover { background: #146e54; transform: translateY(-1px); }

    .barre-controles {
        display: flex; justify-content: space-between; align-items: center;
        gap: 12px; flex-wrap: wrap; margin-bottom: 16px;
    }
    .controles { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0; }
    .champ-recherche {
        position: relative; display: flex; align-items: center;
    }
    .champ-recherche .svg {
        position: absolute; left: 12px; color: #999; pointer-events: none;
    }
    .controles input[type="text"] {
        padding: 10px 14px 10px 36px; width: 260px; max-width: 100%;
    }
    .controles select, .controles input {
        padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 10px;
        font-size: 13px; background: white; font-family: inherit; color: #333;
    }
    .controles select:focus, .controles input:focus {
        outline: none; border-color: #1A8969; box-shadow: 0 0 0 3px rgba(26, 137, 105, 0.12);
    }
    .compteur-resultats { font-size: 13px; color: #777; }
    .compteur-resultats strong { color: #1A8969; }

    .table-wrapper {
        background: white; border-radius: 12px; overflow-x: auto;
        -webkit-overflow-scrolling: touch; border: 1px solid #eef1ef;
    }
    table { width: 100%; background: white; border-collapse: collapse; }
    th, td { padding: 13px 16px; text-align: left; border-bottom: 1px solid #f0f0f0; font-size: 13px; white-space: nowrap; }
    th {
        background: #1A8969; color: white; font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #f7faf8; }
    td.col-ref { font-weight: 700; color: #555; font-size: 12.5px; letter-spacing: 0.3px; }
    td.col-motif { max-width: 240px; overflow: hidden; text-overflow: ellipsis; }
    td.col-salle { font-weight: 600; color: #1A8969; }
    td.col-salle .salle-ville { display: flex; align-items: center; gap: 4px; margin-top: 3px; font-size: 11.5px; font-weight: 500; color: #b8941f; }
    td.col-salle .salle-ville svg { width: 11px; height: 11px; flex-shrink: 0; }

    .badge {
        padding: 4px 12px 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
        white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;
    }
    .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .badge-confirmee { background: #e6f4ea; color: #1e7a3c; }
    .badge-en_attente { background: #fff3cd; color: #8a6d0b; }
    .badge-refusee { background: #fdeaea; color: #c0392b; }
    .badge-annulee { background: #eee; color: #666; }

    .empty { text-align: center; color: #888; padding: 46px 20px; }
    .empty .svg { width: 34px; height: 34px; color: #c5cfca; display: block; margin: 0 auto 10px; }

    /* ===== Alertes (erreurs) ===== */
    .alerte {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; border-radius: 10px; margin-bottom: 16px;
        font-size: 13.5px; font-weight: 600; transition: opacity 0.4s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .alerte .alerte-icone {
        width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        color: white;
    }
    .alerte .alerte-icone .svg { width: 14px; height: 14px; }
    .alerte .alerte-texte { flex: 1; }
    .alerte .alerte-fermer {
        background: none; border: none; cursor: pointer; font-size: 18px;
        line-height: 1; color: inherit; opacity: 0.6; padding: 0 4px;
    }
    .alerte .alerte-fermer:hover { opacity: 1; }
    .alerte-erreur { background: #fdeaea; color: #a94442; border: 1px solid #f3c2c0; }
    .alerte-erreur .alerte-icone { background: #d9534f; }
    .alerte-succes { background: #e6f4ea; color: #1e7a3c; border: 1px solid #bfe0c9; }
    .alerte-succes .alerte-icone { background: #1A8969; }

    /* ===== Liste d'actions (menu déroulant) ===== */
    .actions-dropdown { display: inline-block; }

    .btn-actions .svg { width: 13px; height: 13px; }
    .btn-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #1A8969;
        color: white;
        border: none;
        padding: 7px 16px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
        transition: 0.2s ease;
        white-space: nowrap;
    }
    .btn-actions:hover {
        background: #146e54;
        box-shadow: 0 3px 10px rgba(26, 137, 105, 0.3);
    }

    .menu-actions {
        display: none;
        position: fixed;
        min-width: 180px;
        padding: 6px 0;
        background: white;
        border-radius: 10px;
        border: 1px solid #eef1ef;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        z-index: 900;
        overflow: hidden;
    }
    .menu-actions.ouvert { display: block; }

    .menu-actions .item-action {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 16px;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        text-align: left;
        text-decoration: none;
        transition: 0.15s ease;
    }
    .menu-actions .item-action:hover { background: #f5f7f6; }

    .menu-actions .ic {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .menu-actions .ic .svg { width: 13px; height: 13px; }

    .item-consulter { color: #1565c0; }
    .item-consulter .ic { background: #e3f2fd; }

    .item-pdf { color: #1A8969; }
    .item-pdf .ic { background: #e6f4ea; }

    .item-modifier { color: #b8860b; }
    .item-modifier .ic { background: #fdf3d9; }

    .item-annuler { color: #d9534f; }
    .item-annuler .ic { background: #fdeaea; }
    .menu-actions .item-annuler:hover { background: #fff3f3; }

    .menu-actions .separateur { height: 1px; background: #eee; margin: 4px 0; }

    /* ===== Modale Consulter ===== */
    .modale-fond {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        pointer-events: auto;
    }
    .modale-fond.ouverte { display: flex; }

    .modale-detail-boite {
        background: white; border-radius: 12px; max-width: 520px; width: 100%;
        max-height: 88vh; overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparition 0.2s ease;
    }
    @keyframes apparition {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modale-detail-photo {
        width: 100%; height: 180px; object-fit: cover;
        border-radius: 12px 12px 0 0; background: #eee; display: block;
    }
    .modale-detail-photo-vide {
        width: 100%; height: 120px; display: none; align-items: center; justify-content: center;
        background: #f0f0f0; color: #999; font-size: 13px; border-radius: 12px 12px 0 0;
    }
    .modale-detail-corps { padding: 22px 26px; }
    .modale-detail-titre { font-size: 17px; font-weight: 700; color: #1A8969; margin-bottom: 4px; }
    .modale-detail-soustitre { font-size: 12.5px; color: #888; margin-bottom: 18px; }
    .modale-detail-grille { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; }
    .modale-detail-champ .libelle {
        font-size: 11px; color: #999; text-transform: uppercase; font-weight: 600; margin-bottom: 3px;
    }
    .modale-detail-champ .valeur { font-size: 13.5px; color: #222; font-weight: 500; word-break: break-word; }
    .modale-detail-full { grid-column: 1 / -1; }
    .traite-bloc {
        display: none; align-items: center; gap: 12px; margin-top: 16px;
        padding: 12px 14px; border-radius: 10px;
        background: #fff8e1; border: 1px solid #f0e2b0; border-left: 4px solid #D4AF37;
    }
    .traite-bloc.refusee { background: #fdeaea; border-color: #f3c2c0; border-left-color: #d9534f; }
    .traite-icone {
        width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: #1A8969; color: white;
    }
    .traite-bloc.refusee .traite-icone { background: #d9534f; }
    .traite-icone .svg { width: 17px; height: 17px; }
    .traite-libelle { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #8a6d0b; letter-spacing: .3px; }
    .traite-bloc.refusee .traite-libelle { color: #c0392b; }
    .traite-nom { font-size: 14px; font-weight: 700; color: #222; margin-top: 1px; }
    .traite-date { font-size: 12px; color: #777; margin-top: 1px; }
    .modale-detail-refus {
        margin-top: 16px; background: #fdeaea; color: #d9534f;
        padding: 10px 14px; border-radius: 8px; font-size: 13px; display: none;
    }
    .modale-detail-fermer {
        margin-top: 22px; width: 100%; padding: 10px; border: none; border-radius: 20px;
        background: #1A8969; color: white; font-size: 14px; font-weight: 700; cursor: pointer;
        transition: 0.2s ease;
    }
    .modale-detail-fermer:hover { background: #146e54; }

    /* ===== Modale annulation ===== */
    .modale-action-boite {
        background: white;
        border-radius: 14px;
        max-width: 400px;
        width: 100%;
        padding: 26px 28px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparition 0.2s ease;
    }
    .modale-action-icone .svg { width: 24px; height: 24px; }
    .modale-action-icone {
        width: 50px; height: 50px; border-radius: 50%;
        background: #fdeaea; color: #d9534f;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
    }
    .modale-action-boite h3 { margin: 0 0 8px; color: #1a1a1a; font-size: 17px; }
    .modale-action-boite p { margin: 0 0 20px; color: #777; font-size: 13.5px; line-height: 1.5; }
    .modale-action-boutons { display: flex; gap: 10px; }
    .modale-action-boutons button {
        flex: 1; padding: 9px 0; border-radius: 20px;
        font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: 0.2s ease;
    }
    .btn-action-garder { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-action-garder:hover { background: #f5f5f5; border-color: #999; }
    .btn-action-annuler { background: #d9534f; color: white; border: 1.3px solid #d9534f; }
    .btn-action-annuler:hover { background: #c0392b; border-color: #c0392b; }

    @media (max-width: 600px) {
        .barre-controles { flex-direction: column; align-items: stretch; }
        .controles input[type="text"] { width: 100%; }
        .champ-recherche { width: 100%; }
        .page-header h2 { font-size: 18px; }
    }
    @media (max-width: 500px) {
        .modale-detail-grille { grid-template-columns: 1fr; }
    }
</style>

<div class="page-header">
    <div>
        <h2>Mes demandes</h2>
        <div class="sous-titre">Suivez l'état de vos demandes de réservation de salles.</div>
    </div>
    <div class="entete-droite">
        <a href="{{ route('salles.liste') }}" class="btn-nouvelle"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Nouvelle réservation</a>
    </div>
</div>

<div class="barre-controles">
    <form class="controles" method="GET" action="{{ route('reservations.mes_demandes') }}" onsubmit="return false;">
        <div class="champ-recherche">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" id="rechercheLigne" placeholder="Rechercher une demande...">
        </div>
        <select name="statut" onchange="window.location = '{{ route('reservations.mes_demandes') }}' + (this.value === 'toutes' ? '' : '?statut=' + this.value)">
            <option value="toutes" @selected($statut == 'toutes')>Tous les statuts</option>
            <option value="en_attente" @selected($statut == 'en_attente')>En attente</option>
            <option value="confirmee" @selected($statut == 'confirmee')>Confirmées</option>
            <option value="refusee" @selected($statut == 'refusee')>Refusées</option>
            <option value="annulee" @selected($statut == 'annulee')>Annulées</option>
        </select>
    </form>
    <div class="compteur-resultats"><strong id="nbResultats">{{ $demandes->count() }}</strong> demande(s)</div>
</div>

@if (session('success'))
    <div class="alerte alerte-succes">
        <span class="alerte-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span>
        <span class="alerte-texte">{{ session('success') }}</span>
        <button type="button" class="alerte-fermer" onclick="fermerAlerte(this)">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div class="alerte alerte-erreur">
        <span class="alerte-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v5M12 17h.01"/></svg></span>
        <span class="alerte-texte">{{ $errors->first() }}</span>
        <button type="button" class="alerte-fermer" onclick="fermerAlerte(this)">&times;</button>
    </div>
@endif

@php
    $libellesStatut = [
        'en_attente' => 'En attente',
        'confirmee'  => 'Confirmée',
        'refusee'    => 'Refusée',
        'annulee'    => 'Annulée',
    ];
    $libellesRole = ['agent' => 'Agent', 'gestionnaire' => 'Gestionnaire'];
@endphp

<div class="table-wrapper">
    <table id="tableDemandes">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Salle</th>
                <th>Motif</th>
                <th>Période</th>
                <th>Horaire</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($demandes as $r)
            @php
                $debut = \Carbon\Carbon::parse($r->date_reserv);
                $fin = $r->date_fin ? \Carbon\Carbon::parse($r->date_fin) : null;
                $periode = ($fin && ! $fin->isSameDay($debut))
                    ? $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y')
                    : $debut->format('d/m/Y');

                $hd = $r->heure_debut ? substr($r->heure_debut, 0, 5) : null;
                $hf = $r->heure_fin ? substr($r->heure_fin, 0, 5) : null;
                $horaire = ($hd && $hf) ? $hd . ' - ' . $hf
                    : ($hd ? 'À partir de ' . $hd
                    : ($hf ? 'Jusqu\'à ' . $hf : 'Journée entière'));

                $libelleStatut = $libellesStatut[$r->statut] ?? ucfirst(str_replace('_', ' ', $r->statut));

                // Personne qui a traité la demande (validation ou refus)
                $valideur = $r->traitePar;
                $traiteActif = in_array($r->statut, ['confirmee', 'refusee'], true);
                $libelleTraite = $r->statut === 'refusee' ? 'Refusée par' : 'Validée par';
                $roleValideur = $valideur
                    ? ($valideur->role === 'admin'
                        ? $valideur->libelle_role
                        : ($libellesRole[$valideur->role] ?? ucfirst($valideur->role)))
                    : '';
                $dateTraite = $r->traite_le ? \Carbon\Carbon::parse($r->traite_le)->format('d/m/Y à H:i') : '';
            @endphp
            <tr>
                <td class="col-ref">{{ $r->reference }}</td>
                <td class="col-salle">
                    {{ $r->salle->nom ?? 'Salle supprimée' }}
                    @if (! empty($r->salle?->localite))
                        <div class="salle-ville">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                            {{ $r->salle->localite }}
                        </div>
                    @endif
                </td>
                <td class="col-motif" title="{{ $r->motif }}">{{ $r->motif }}</td>
                <td>{{ $periode }}</td>
                <td>{{ $horaire }}</td>
                <td><span class="badge badge-{{ $r->statut }}">{{ $libelleStatut }}</span></td>
                <td>
                    <div class="actions-dropdown">
                        <button type="button" class="btn-actions" onclick="basculerMenu(this)">Actions <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>

                        <div class="menu-actions">
                            <button type="button"
                                    class="item-action item-consulter"
                                    data-reference="{{ $r->reference }}"
                                    data-salle="{{ $r->salle->nom ?? 'Salle supprimée' }}"
                                    data-salle-ref="{{ $r->salle->reference ?? '-' }}"
                                    data-localite="{{ $r->salle->localite ?? '-' }}"
                                    data-capacite="{{ $r->salle->capacite ?? '-' }}"
                                    data-photo="{{ ($r->salle && $r->salle->photo) ? asset('storage/' . $r->salle->photo) : '' }}"
                                    data-motif="{{ $r->motif }}"
                                    data-periode="{{ $periode }}"
                                    data-horaire="{{ $horaire }}"
                                    data-participants="{{ $r->nombre ?? '-' }}"
                                    data-statut="{{ $libelleStatut }}"
                                    data-statut-classe="{{ $r->statut }}"
                                    data-depose="{{ $r->created_at->format('d/m/Y à H:i') }}"
                                    data-traite-actif="{{ $traiteActif ? '1' : '' }}"
                                    data-traite-libelle="{{ $libelleTraite }}"
                                    data-traite-par="{{ $valideur->name ?? '' }}"
                                    data-traite-role="{{ $roleValideur }}"
                                    data-traite-le="{{ $dateTraite }}"
                                    data-motif-refus="{{ $r->statut === 'refusee' ? ($r->motif_refus ?? '') : '' }}"
                                    onclick="ouvrirDetail(this)">
                                <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></span> Consulter
                            </button>

                            @if ($r->statut === 'confirmee')
                                <a href="{{ route('reservations.attestation', ['reservation' => $r, 'telecharger' => 1]) }}" class="item-action item-pdf">
                                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg></span> Attestation PDF
                                </a>
                            @endif

                            @if ($r->statut === 'en_attente')
                                <a href="{{ route('reservations.edit', $r) }}" class="item-action item-modifier">
                                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg></span> Modifier
                                </a>

                                <div class="separateur"></div>

                                <button type="button" class="item-action item-annuler" onclick="ouvrirModaleAnnulation({{ $r->id }})">
                                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></span> Annuler
                                </button>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>Aucune demande pour ce filtre.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modale Consulter -->
<div id="modaleDetail" class="modale-fond">
    <div class="modale-detail-boite">
        <img id="detailPhoto" class="modale-detail-photo" alt="Photo de la salle" style="display:none;">
        <div id="detailPhotoVide" class="modale-detail-photo-vide">Pas de photo disponible</div>

        <div class="modale-detail-corps">
            <div class="modale-detail-titre" id="detailSalle"></div>
            <div class="modale-detail-soustitre" id="detailSousTitre"></div>

            <div class="modale-detail-grille">
                <div class="modale-detail-champ">
                    <div class="libelle">Référence</div>
                    <div class="valeur" id="detailReference"></div>
                </div>
                <div class="modale-detail-champ">
                    <div class="libelle">Statut</div>
                    <div class="valeur"><span class="badge" id="detailStatut"></span></div>
                </div>
                <div class="modale-detail-champ modale-detail-full">
                    <div class="libelle">Motif</div>
                    <div class="valeur" id="detailMotif"></div>
                </div>
                <div class="modale-detail-champ">
                    <div class="libelle">Période</div>
                    <div class="valeur" id="detailPeriode"></div>
                </div>
                <div class="modale-detail-champ">
                    <div class="libelle">Horaire</div>
                    <div class="valeur" id="detailHoraire"></div>
                </div>
                <div class="modale-detail-champ">
                    <div class="libelle">Participants</div>
                    <div class="valeur" id="detailParticipants"></div>
                </div>
                <div class="modale-detail-champ">
                    <div class="libelle">Demande déposée le</div>
                    <div class="valeur" id="detailDepose"></div>
                </div>
            </div>

            <div class="traite-bloc" id="detailTraiteChamp">
                <div class="traite-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></div>
                <div>
                    <div class="traite-libelle" id="detailTraiteLibelle"></div>
                    <div class="traite-nom" id="detailTraitePar"></div>
                    <div class="traite-date" id="detailTraiteLe"></div>
                </div>
            </div>

            <div class="modale-detail-refus" id="detailRefus"></div>

            <button type="button" class="modale-detail-fermer" onclick="fermerDetail()">Fermer</button>
        </div>
    </div>
</div>

<!-- Modale annulation -->
<div id="modaleAnnulation" class="modale-fond">
    <div class="modale-action-boite">
        <div class="modale-action-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></div>
        <h3>Annuler cette réservation ?</h3>
        <p>Voulez-vous vraiment annuler cette demande ? Cette action est irréversible.</p>

        <form id="formAnnulation" method="POST" action="">
            @csrf
            <div class="modale-action-boutons">
                <button type="button" class="btn-action-garder" onclick="fermerModaleAnnulation()">Non, garder</button>
                <button type="submit" class="btn-action-annuler">Oui, annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
/* ----- Alertes ----- */
function fermerAlerte(bouton) {
    const alerte = bouton.closest('.alerte');
    alerte.style.opacity = '0';
    setTimeout(function () { alerte.remove(); }, 400);
}

/* ----- Liste d'actions (menu déroulant) ----- */
function fermerTousLesMenus() {
    document.querySelectorAll('.menu-actions.ouvert').forEach(function (m) {
        m.classList.remove('ouvert');
    });
}

function basculerMenu(bouton) {
    const menu = bouton.nextElementSibling;
    const etaitOuvert = menu.classList.contains('ouvert');

    fermerTousLesMenus();
    if (etaitOuvert) return;

    menu.classList.add('ouvert');

    // Position : sous le bouton, ou au-dessus s'il n'y a pas la place
    const r = bouton.getBoundingClientRect();
    const h = menu.offsetHeight;
    const w = menu.offsetWidth;

    let top = r.bottom + 6;
    if (top + h > window.innerHeight - 8) top = r.top - h - 6;
    let left = r.right - w;
    if (left < 8) left = 8;

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.actions-dropdown')) fermerTousLesMenus();
});
window.addEventListener('scroll', fermerTousLesMenus, true);
window.addEventListener('resize', fermerTousLesMenus);

/* ----- Modale Consulter ----- */
function ouvrirDetail(bouton) {
    fermerTousLesMenus();
    const d = bouton.dataset;

    document.getElementById('detailSalle').textContent = d.salle;
    document.getElementById('detailSousTitre').textContent =
        d.salleRef + ' · ' + d.localite + ' · Capacité : ' + d.capacite + ' places';
    document.getElementById('detailReference').textContent = d.reference;
    document.getElementById('detailMotif').textContent = d.motif;
    document.getElementById('detailPeriode').textContent = d.periode;
    document.getElementById('detailHoraire').textContent = d.horaire;
    document.getElementById('detailParticipants').textContent = d.participants;
    document.getElementById('detailDepose').textContent = d.depose;

    const statut = document.getElementById('detailStatut');
    statut.textContent = d.statut;
    statut.className = 'badge badge-' + d.statutClasse;

    // Qui a validé ou refusé la demande
    const traite = document.getElementById('detailTraiteChamp');
    if (d.traiteActif) {
        document.getElementById('detailTraiteLibelle').textContent = d.traiteLibelle;
        document.getElementById('detailTraitePar').textContent = d.traitePar
            ? d.traitePar + (d.traiteRole ? ' (' + d.traiteRole + ')' : '')
            : 'Non renseigné';
        document.getElementById('detailTraiteLe').textContent = d.traiteLe ? 'Le ' + d.traiteLe : '';
        traite.classList.toggle('refusee', d.statutClasse === 'refusee');
        traite.style.display = 'flex';
    } else {
        traite.style.display = 'none';
    }

    const refus = document.getElementById('detailRefus');
    if (d.motifRefus) {
        refus.textContent = 'Motif du refus : ' + d.motifRefus;
        refus.style.display = 'block';
    } else {
        refus.style.display = 'none';
    }

    const photo = document.getElementById('detailPhoto');
    const vide = document.getElementById('detailPhotoVide');
    if (d.photo) {
        photo.onerror = function () { photo.style.display = 'none'; vide.style.display = 'flex'; };
        photo.src = d.photo;
        photo.style.display = 'block';
        vide.style.display = 'none';
    } else {
        photo.style.display = 'none';
        vide.style.display = 'flex';
    }

    document.getElementById('modaleDetail').classList.add('ouverte');
}
function fermerDetail() {
    document.getElementById('modaleDetail').classList.remove('ouverte');
}
document.getElementById('modaleDetail').addEventListener('click', function (e) {
    if (e.target === this) fermerDetail();
});

/* ----- Modale annulation ----- */
function ouvrirModaleAnnulation(reservationId) {
    fermerTousLesMenus();
    document.getElementById('formAnnulation').action =
        "{{ route('reservations.annuler', ['reservation' => '__ID__']) }}".replace('__ID__', reservationId);
    document.getElementById('modaleAnnulation').classList.add('ouverte');
}
function fermerModaleAnnulation() {
    document.getElementById('modaleAnnulation').classList.remove('ouverte');
}
document.getElementById('modaleAnnulation').addEventListener('click', function (e) {
    if (e.target === this) fermerModaleAnnulation();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        fermerTousLesMenus();
        fermerDetail();
        fermerModaleAnnulation();
    }
});

/* ----- Recherche (la colonne Actions est ignorée) ----- */
document.getElementById('rechercheLigne').addEventListener('input', function () {
    const recherche = this.value.toLowerCase();
    let visibles = 0;
    document.querySelectorAll('#tableDemandes tbody tr').forEach(function (ligne) {
        if (ligne.querySelector('.empty')) return;
        const texte = Array.from(ligne.children).slice(0, -1)
            .map(function (td) { return td.textContent; })
            .join(' ')
            .toLowerCase();
        const ok = texte.includes(recherche);
        ligne.style.display = ok ? '' : 'none';
        if (ok) visibles++;
    });
    document.getElementById('nbResultats').textContent = visibles;
});
</script>
@endsection