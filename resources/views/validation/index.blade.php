@extends('layouts.dgi')

@section('title', 'Demandes à valider - DGI')

@section('content')
<style>
    .svg { width: 16px; height: 16px; flex-shrink: 0; }

    .page-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
    }
    .page-header h2 { color: #1A8969; font-size: 18px; }

    .barre-controles {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 10px; flex-wrap: wrap; gap: 10px;
    }
    .barre-controles select, .barre-controles input, .page-header select {
        padding: 7px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; background: white; font-family: inherit;
    }
    .controle-gauche, .controle-droite { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; flex-wrap: wrap; }
    .texte-filtre { font-size: 13px; color: #555; }

    .btn-export {
        display: inline-flex; align-items: center; gap: 7px;
        background: white; color: #1A8969; text-decoration: none;
        border: 1.3px solid #1A8969; border-radius: 20px;
        padding: 7px 16px; font-size: 12.5px; font-weight: 700; font-family: inherit;
        transition: 0.2s ease; white-space: nowrap;
    }
    .btn-export .svg { width: 15px; height: 15px; }
    .btn-export:hover { background: #1A8969; color: white; }

    .table-wrapper {
        background: white;
        border-radius: 8px;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05), 0 16px 20px -16px rgba(212, 175, 55, 0.45);
    }

    table {
        width: 100%;
        table-layout: fixed;
        background: white;
        border-radius: 8px;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px 14px;
        text-align: left;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        word-wrap: break-word;
        vertical-align: top;
    }
    th { background: #1A8969; color: white; font-size: 12px; cursor: pointer; user-select: none; white-space: nowrap; }
    th.non-triable { cursor: default; }
    th.tri-asc::after { content: " ▲"; font-size: 10px; }
    th.tri-desc::after { content: " ▼"; font-size: 10px; }

    th:nth-child(1), td:nth-child(1) { width: 12%; white-space: nowrap; }   /* Référence */
    th:nth-child(2), td:nth-child(2) { width: 10%; }  /* Agent */
    th:nth-child(3), td:nth-child(3) { width: 12%; }  /* Direction / Service */
    th:nth-child(4), td:nth-child(4) { width: 13%; }  /* Salle */
    th:nth-child(5), td:nth-child(5) { width: 10%; }  /* Motif */
    th:nth-child(6), td:nth-child(6) { width: 12%; }  /* Période */
    th:nth-child(7), td:nth-child(7) { width: 9%; }   /* Horaire */
    th:nth-child(8), td:nth-child(8) { width: 11%; }  /* Statut */
    th:nth-child(9), td:nth-child(9) { width: 11%; white-space: nowrap; }  /* Actions */

    .badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; display: inline-block; }
    .badge-confirmee { background: #d4edda; color: #155724; }
    .badge-en_attente { background: #fff3cd; color: #856404; }
    .badge-refusee { background: #f8d7da; color: #721c24; }
    .badge-annulee { background: #eee; color: #666; }

    .traite-par { font-size: 11px; color: #777; margin-top: 5px; line-height: 1.3; }
    .traite-par strong { color: #333; }

    /* ===== Liste d'actions (menu déroulant) ===== */
    .actions-dropdown { display: inline-block; }

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
        font-family: inherit;
        transition: 0.2s ease;
        white-space: nowrap;
    }
    .btn-actions:hover {
        background: #146e54;
        box-shadow: 0 3px 10px rgba(26, 137, 105, 0.3);
    }
    .btn-actions .svg { width: 14px; height: 14px; }

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
        transition: 0.15s ease;
    }
    .menu-actions .item-action:hover { background: #f5f7f6; }

    .menu-actions .ic {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .menu-actions .ic .svg { width: 13px; height: 13px; }

    .item-consulter { color: #1565c0; }
    .item-consulter .ic { background: #e3f2fd; }

    .item-valider { color: #1A8969; }
    .item-valider .ic { background: #e8f5e9; }
    .menu-actions .item-valider:hover { background: #f0f8f2; }

    .item-refuser { color: #d9534f; }
    .item-refuser .ic { background: #fdeaea; }
    .menu-actions .item-refuser:hover { background: #fff3f3; }

    .menu-actions .separateur { height: 1px; background: #eee; margin: 4px 0; }

    .pagination-zone {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 12px; font-size: 13px; color: #555; flex-wrap: wrap; gap: 10px;
    }
    .pagination-boutons button {
        padding: 6px 12px; margin-left: 4px; border: 1px solid #ccc; background: white;
        border-radius: 6px; cursor: pointer; font-size: 12px;
    }
    .pagination-boutons button.actif { background: #1A8969; color: white; border-color: #1A8969; }
    .pagination-boutons button:disabled { opacity: 0.4; cursor: default; }

    .error-box {
        background: #fdeaea; color: #d9534f; padding: 10px 16px;
        border-radius: 8px; margin-bottom: 15px; font-size: 14px;
    }
    .empty { text-align: center; color: #666; padding: 40px; }

    /* ===== Modale Consulter ===== */
    .modale-detail-fond {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;
        padding: 20px; pointer-events: auto;
    }
    .modale-detail-fond.ouverte { display: flex; }
    .modale-detail-boite {
        background: white; border-radius: 10px; max-width: 520px; width: 100%;
        max-height: 85vh; overflow-y: auto; padding: 0; text-align: left; white-space: normal;
    }
    .modale-detail-photo {
        width: 100%; height: 180px; object-fit: cover;
        border-radius: 10px 10px 0 0; background: #eee; display: block;
    }
    .modale-detail-photo-vide {
        width: 100%; height: 180px; display: flex; align-items: center; justify-content: center;
        background: #f0f0f0; color: #999; font-size: 13px; border-radius: 10px 10px 0 0;
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
    .modale-detail-refus {
        margin-top: 16px; background: #fdeaea; color: #d9534f;
        padding: 10px 14px; border-radius: 8px; font-size: 13px;
    }
    .modale-detail-fermer {
        margin-top: 22px; width: 100%; padding: 10px; border: none; border-radius: 20px;
        background: #1A8969; color: white; font-size: 14px; font-weight: 700; cursor: pointer;
        font-family: inherit; transition: 0.2s ease;
    }
    .modale-detail-fermer:hover { background: #146e54; }

    /* ===== Modales validation / refus ===== */
    .modale-fond-action {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.55);
        z-index: 1100;
        align-items: center;
        justify-content: center;
        padding: 20px;
        pointer-events: auto;
    }
    .modale-fond-action.ouverte { display: flex; }

    .modale-action-boite {
        background: white;
        border-radius: 14px;
        max-width: 400px;
        width: 100%;
        padding: 26px 28px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparitionAction 0.2s ease;
    }
    @keyframes apparitionAction {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modale-action-icone {
        width: 50px; height: 50px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
    }
    .modale-action-icone .svg { width: 24px; height: 24px; }
    .icone-valider { background: #e8f5e9; color: #1A8969; }
    .icone-refuser { background: #fdeaea; color: #d9534f; }

    .modale-action-boite h3 { margin: 0 0 8px; color: #1a1a1a; font-size: 17px; }
    .modale-action-boite p { margin: 0 0 20px; color: #777; font-size: 13.5px; line-height: 1.5; }

    .modale-action-boite textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1.3px solid #ccc;
        border-radius: 8px;
        font-size: 13.5px;
        font-family: inherit;
        resize: vertical;
        margin-bottom: 18px;
        text-align: left;
    }
    .modale-action-boite textarea:focus { outline: none; border-color: #d9534f; }

    .modale-action-boutons { display: flex; gap: 10px; }
    .modale-action-boutons button {
        flex: 1; padding: 9px 0; border-radius: 20px;
        font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: 0.2s ease;
    }
    .btn-action-annuler { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-action-annuler:hover { background: #f5f5f5; border-color: #999; }
    .btn-action-valider { background: #1A8969; color: white; border: 1.3px solid #1A8969; }
    .btn-action-valider:hover { background: #146e54; border-color: #146e54; }
    .btn-action-refuser { background: #d9534f; color: white; border: 1.3px solid #d9534f; }
    .btn-action-refuser:hover { background: #c0392b; border-color: #c0392b; }

    @media (max-width: 500px) {
        .modale-detail-grille { grid-template-columns: 1fr; }
    }
</style>

@php
    $libellesStatut = [
        'en_attente' => 'En attente',
        'confirmee'  => 'Confirmée',
        'refusee'    => 'Refusée',
        'annulee'    => 'Annulée',
    ];
    $libellesRole = ['agent' => 'Agent', 'gestionnaire' => 'Gestionnaire'];
@endphp

<div class="page-header">
    <h2>Demandes de réservation</h2>

    <form method="GET" action="{{ route('validation.index') }}" style="display:flex; align-items:center; gap:8px;">
        <span class="texte-filtre">Statut :</span>
        <select id="filtreStatut" name="statut" aria-label="Filtrer par statut" onchange="this.form.submit()">
            <option value="toutes" @selected($statut == 'toutes')>Tous les statuts</option>
            <option value="en_attente" @selected($statut == 'en_attente')>En attente</option>
            <option value="confirmee" @selected($statut == 'confirmee')>Confirmées</option>
            <option value="refusee" @selected($statut == 'refusee')>Refusées</option>
            <option value="annulee" @selected($statut == 'annulee')>Annulées</option>
        </select>
    </form>
</div>

@if ($errors->any())
    <div class="error-box">{{ $errors->first() }}</div>
@endif

<div class="barre-controles">
    <div class="controle-gauche">
        <span>Afficher</span>
        <select id="parPage" aria-label="Nombre de demandes par page">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span>demandes</span>
    </div>
    <div class="controle-droite">
        <a href="{{ route('validation.export', ['statut' => $statut]) }}" id="btnExport" class="btn-export">
            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13l4 4M13 13l-4 4"/></svg>
            Exporter en Excel
        </a>
        <span>Rechercher :</span>
        <input type="text" id="rechercheTable" placeholder="Rechercher..." aria-label="Rechercher dans les demandes">
    </div>
</div>

<div class="table-wrapper">
    <table id="tableValidation">
        <thead>
            <tr>
                <th data-col="0">Référence</th>
                <th data-col="1">Agent</th>
                <th data-col="2">Direction / Service</th>
                <th data-col="3">Salle</th>
                <th data-col="4">Motif</th>
                <th data-col="5">Période</th>
                <th data-col="6">Horaire</th>
                <th class="non-triable">Statut</th>
                <th class="non-triable">Actions</th>
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

                // Une demande en attente dont la date est dépassée est considérée comme refusée
                $dateLimite = ($fin ?? $debut)->copy()->endOfDay();
                $estExpiree = $r->statut === 'en_attente' && $dateLimite->isPast();
                $statutAffiche = $estExpiree ? 'refusee' : $r->statut;
                $libelleStatut = $libellesStatut[$statutAffiche] ?? ucfirst(str_replace('_', ' ', $statutAffiche));

                $nomDirection = is_object($r->direction) ? ($r->direction->nom_direction ?? '-') : ($r->direction ?? '-');
                $nomService = is_object($r->service) ? ($r->service->nom_service ?? '-') : ($r->service ?? '-');

                // Personne qui a traité la demande (validation ou refus)
                $traiteActif = in_array($r->statut, ['confirmee', 'refusee'], true);
                $valideur = $r->traitePar;
                $roleTraite = $valideur
                    ? ($valideur->role === 'admin'
                        ? $valideur->libelle_role
                        : ($libellesRole[$valideur->role] ?? ucfirst($valideur->role)))
                    : '';
                $dateTraite = $r->traite_le ? \Carbon\Carbon::parse($r->traite_le)->format('d/m/Y à H:i') : '';
                $traiteNom = $valideur->name ?? (
                    ($r->statut === 'refusee' && str_starts_with((string) $r->motif_refus, 'Salle indisponible'))
                        ? 'le système (créneau déjà confirmé)'
                        : 'non renseigné'
                );
            @endphp
            <tr>
                <td>{{ $r->reference }}</td>
                <td>{{ $r->utilisateur->name ?? 'Utilisateur supprimé' }}</td>
                <td>{{ $nomDirection }} / {{ $nomService }}</td>
                <td>{{ $r->salle->nom ?? 'Salle supprimée' }} ({{ $r->salle->localite ?? '-' }})</td>
                <td>{{ $r->motif }}</td>
                <td>{{ $periode }}</td>
                <td>{{ $horaire }}</td>
                <td>
                    <span class="badge badge-{{ $statutAffiche }}">{{ $libelleStatut }}</span>
                    @if ($traiteActif)
                        <div class="traite-par">{{ $r->statut === 'confirmee' ? 'Validée' : 'Refusée' }} par <strong>{{ $traiteNom }}</strong></div>
                    @endif
                </td>
                <td>
                    <div class="actions-dropdown">
                        <button type="button" class="btn-actions" onclick="basculerMenu(this)">Actions <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>
                        <div class="menu-actions">
                            <button type="button" class="item-action item-consulter" onclick="ouvrirDetail({{ $r->id }})">
                                <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></span> Consulter
                            </button>

                            @if ($r->statut === 'en_attente' && ! $estExpiree)
                                <div class="separateur"></div>

                                <button type="button" class="item-action item-valider" onclick="ouvrirModaleValidation({{ $r->id }})">
                                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg></span> Valider
                                </button>
                                <button type="button" class="item-action item-refuser" onclick="ouvrirModaleRefus({{ $r->id }})">
                                    <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></span> Refuser
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Modale de détail de cette demande (placée dans la cellule pour garder un tableau valide) --}}
                    <div id="modaleDetail{{ $r->id }}" class="modale-detail-fond">
                        <div class="modale-detail-boite">
                            @if ($r->salle && $r->salle->photo)
                                <img src="{{ asset('storage/' . $r->salle->photo) }}" alt="Photo de la salle" class="modale-detail-photo">
                            @else
                                <div class="modale-detail-photo-vide">Pas de photo disponible</div>
                            @endif

                            <div class="modale-detail-corps">
                                <div class="modale-detail-titre">{{ $r->salle->nom ?? 'Salle supprimée' }}</div>
                                <div class="modale-detail-soustitre">
                                    {{ $r->salle->reference ?? '-' }} · {{ $r->salle->localite ?? '-' }} · Capacité : {{ $r->salle->capacite ?? '-' }} places
                                </div>
                                <div class="modale-detail-grille">
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Référence demande</div>
                                        <div class="valeur">{{ $r->reference }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Référence agent</div>
                                        <div class="valeur">{{ $r->utilisateur->reference ?? '-' }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Agent demandeur</div>
                                        <div class="valeur">{{ $r->utilisateur->name ?? 'Utilisateur supprimé' }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Email</div>
                                        <div class="valeur">{{ $r->email ?? '-' }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Direction / Service</div>
                                        <div class="valeur">{{ $nomDirection }} / {{ $nomService }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Motif de réservation</div>
                                        <div class="valeur">{{ $r->motif }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Période</div>
                                        <div class="valeur">{{ $periode }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Horaire</div>
                                        <div class="valeur">{{ $horaire }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Participants demandés</div>
                                        <div class="valeur">{{ $r->nombre ?? '-' }}</div>
                                    </div>
                                    <div class="modale-detail-champ">
                                        <div class="libelle">Demande déposée le</div>
                                        <div class="valeur">{{ $r->created_at->format('d/m/Y à H:i') }}</div>
                                    </div>
                                    <div class="modale-detail-champ modale-detail-full">
                                        <div class="libelle">Statut</div>
                                        <div class="valeur">
                                            <span class="badge badge-{{ $statutAffiche }}">{{ $libelleStatut }}</span>
                                        </div>
                                    </div>
                                    @if ($traiteActif)
                                        <div class="modale-detail-champ modale-detail-full">
                                            <div class="libelle">{{ $r->statut === 'confirmee' ? 'Validée par' : 'Refusée par' }}</div>
                                            <div class="valeur">
                                                {{ $traiteNom }}@if ($roleTraite) ({{ $roleTraite }})@endif
                                                @if ($dateTraite) · {{ $dateTraite }} @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($estExpiree)
                                    <div class="modale-detail-refus">Délai dépassé sans traitement</div>
                                @elseif ($r->statut === 'refusee' && $r->motif_refus)
                                    <div class="modale-detail-refus">Motif du refus : {{ $r->motif_refus }}</div>
                                @endif

                                <button type="button" class="modale-detail-fermer" onclick="fermerDetail({{ $r->id }})">Fermer</button>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="empty">Aucune demande pour ce filtre.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-zone">
    <div id="infoPagination"></div>
    <div class="pagination-boutons" id="boutonsPagination"></div>
</div>

<form id="formValider" method="POST" action="">
    @csrf
    <input type="hidden" name="action" value="valider">
</form>

<!-- Modale de confirmation de validation -->
<div id="modaleValidation" class="modale-fond-action">
    <div class="modale-action-boite">
        <div class="modale-action-icone icone-valider"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg></div>
        <h3>Valider cette demande ?</h3>
        <p>La réservation sera confirmée et l'agent sera notifié. Les autres demandes en attente sur le même créneau seront refusées automatiquement.</p>
        <div class="modale-action-boutons">
            <button type="button" class="btn-action-annuler" onclick="fermerModaleValidation()">Annuler</button>
            <button type="button" class="btn-action-valider" onclick="confirmerValidation()">Valider</button>
        </div>
    </div>
</div>

<!-- Modale de refus -->
<div id="modaleRefus" class="modale-fond-action">
    <div class="modale-action-boite">
        <div class="modale-action-icone icone-refuser"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></div>
        <h3>Refuser cette demande</h3>
        <p>Indiquez le motif du refus. Il sera communiqué à l'agent.</p>
        <form id="formRefus" method="POST" action="">
            @csrf
            <input type="hidden" name="action" value="refuser">
            <textarea name="motif_refus" rows="4" placeholder="Expliquez pourquoi cette demande est refusée..." required></textarea>
            <div class="modale-action-boutons">
                <button type="button" class="btn-action-annuler" onclick="fermerModaleRefus()">Annuler</button>
                <button type="submit" class="btn-action-refuser">Confirmer le refus</button>
            </div>
        </form>
    </div>
</div>

<script>
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

/* ----- Validation ----- */
let idAValider = null;

function ouvrirModaleValidation(id) {
    fermerTousLesMenus();
    idAValider = id;
    document.getElementById('modaleValidation').classList.add('ouverte');
}
function fermerModaleValidation() {
    idAValider = null;
    document.getElementById('modaleValidation').classList.remove('ouverte');
}
function confirmerValidation() {
    if (!idAValider) return;
    const form = document.getElementById('formValider');
    form.action = '/valider/' + idAValider;
    form.submit();
}
document.getElementById('modaleValidation').addEventListener('click', function (e) {
    if (e.target === this) fermerModaleValidation();
});

/* ----- Refus ----- */
function ouvrirModaleRefus(id) {
    fermerTousLesMenus();
    document.getElementById('formRefus').action = '/valider/' + id;
    document.getElementById('modaleRefus').classList.add('ouverte');
}
function fermerModaleRefus() {
    document.getElementById('modaleRefus').classList.remove('ouverte');
}
document.getElementById('modaleRefus').addEventListener('click', function (e) {
    if (e.target === this) fermerModaleRefus();
});

/* ----- Détail (Consulter) ----- */
function ouvrirDetail(id) {
    fermerTousLesMenus();
    document.getElementById('modaleDetail' + id).classList.add('ouverte');
}
function fermerDetail(id) {
    document.getElementById('modaleDetail' + id).classList.remove('ouverte');
}
document.querySelectorAll('.modale-detail-fond').forEach(function (fond) {
    fond.addEventListener('click', function (e) {
        if (e.target === fond) fond.classList.remove('ouverte');
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        fermerTousLesMenus();
        fermerModaleValidation();
        fermerModaleRefus();
        document.querySelectorAll('.modale-detail-fond.ouverte').forEach(function (f) {
            f.classList.remove('ouverte');
        });
    }
});

/* ----- Export Excel : transmet le statut et la recherche en cours ----- */
document.getElementById('btnExport').addEventListener('click', function () {
    const url = new URL(this.href);
    const recherche = document.getElementById('rechercheTable').value.trim();
    if (recherche) { url.searchParams.set('recherche', recherche); } else { url.searchParams.delete('recherche'); }
    this.href = url.toString();
});

/* ----- Recherche, tri et pagination côté client ----- */
const table = document.getElementById('tableValidation');
const tbody = table.querySelector('tbody');
let lignes = Array.from(tbody.querySelectorAll('tr')).filter(function (tr) { return !tr.querySelector('.empty'); });
let pageActuelle = 1;
let colonneTri = null;
let sensTri = 1;

// La colonne Actions (et les modales qu'elle contient) n'entre pas dans la recherche
function texteRecherche(tr) {
    return Array.from(tr.children).slice(0, -1)
        .map(function (td) { return td.textContent; })
        .join(' ')
        .toLowerCase();
}

function appliquerFiltre() {
    const recherche = document.getElementById('rechercheTable').value.toLowerCase();
    return lignes.filter(function (tr) { return texteRecherche(tr).includes(recherche); });
}

function afficherPage() {
    const parPage = parseInt(document.getElementById('parPage').value, 10);
    const filtrees = appliquerFiltre();
    const totalPages = Math.max(1, Math.ceil(filtrees.length / parPage));
    if (pageActuelle > totalPages) pageActuelle = totalPages;

    lignes.forEach(function (tr) { tr.style.display = 'none'; });

    const debut = (pageActuelle - 1) * parPage;
    filtrees.slice(debut, debut + parPage).forEach(function (tr) { tr.style.display = ''; });

    document.getElementById('infoPagination').textContent =
        filtrees.length === 0 ? 'Aucun résultat' :
        'Affichage de ' + (debut + 1) + ' à ' + Math.min(debut + parPage, filtrees.length) + ' sur ' + filtrees.length + ' demandes';

    const boutons = document.getElementById('boutonsPagination');
    boutons.innerHTML = '';
    const precedent = document.createElement('button');
    precedent.textContent = 'Précédent';
    precedent.disabled = pageActuelle === 1;
    precedent.onclick = function () { pageActuelle--; afficherPage(); };
    boutons.appendChild(precedent);

    for (let p = 1; p <= totalPages; p++) {
        const b = document.createElement('button');
        b.textContent = p;
        if (p === pageActuelle) b.classList.add('actif');
        b.onclick = function () { pageActuelle = p; afficherPage(); };
        boutons.appendChild(b);
    }

    const suivant = document.createElement('button');
    suivant.textContent = 'Suivant';
    suivant.disabled = pageActuelle === totalPages;
    suivant.onclick = function () { pageActuelle++; afficherPage(); };
    boutons.appendChild(suivant);
}

document.getElementById('rechercheTable').addEventListener('input', function () {
    pageActuelle = 1;
    afficherPage();
});
document.getElementById('parPage').addEventListener('change', function () {
    pageActuelle = 1;
    afficherPage();
});

table.querySelectorAll('th[data-col]').forEach(function (th) {
    th.addEventListener('click', function () {
        const col = parseInt(th.dataset.col, 10);
        if (colonneTri === col) { sensTri *= -1; } else { colonneTri = col; sensTri = 1; }

        table.querySelectorAll('th').forEach(function (h) { h.classList.remove('tri-asc', 'tri-desc'); });
        th.classList.add(sensTri === 1 ? 'tri-asc' : 'tri-desc');

        lignes.sort(function (a, b) {
            const va = a.children[col].textContent.trim().toLowerCase();
            const vb = b.children[col].textContent.trim().toLowerCase();
            return va.localeCompare(vb, undefined, { numeric: true }) * sensTri;
        });
        lignes.forEach(function (tr) { tbody.appendChild(tr); });
        pageActuelle = 1;
        afficherPage();
    });
});

afficherPage();
</script>
@endsection