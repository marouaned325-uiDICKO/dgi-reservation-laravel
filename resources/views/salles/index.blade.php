@extends('layouts.dgi')

@section('title', 'Gestion des salles - DGI')

@section('content')

@php
    // Réservations confirmées en cours ou à venir, regroupées par salle
    $occupations = \App\Models\Reservation::where('statut', 'confirmee')
        ->whereRaw('COALESCE(date_fin, date_reserv) >= ?', [now()->toDateString()])
        ->orderBy('date_reserv')
        ->get()
        ->groupBy('salle_id');

    $totalReservations = \App\Models\Reservation::selectRaw('salle_id, COUNT(*) as total')
        ->groupBy('salle_id')
        ->pluck('total', 'salle_id');

    $libelleOccupation = function ($r) {
        $debut = \Carbon\Carbon::parse($r->date_reserv);
        $fin = $r->date_fin ? \Carbon\Carbon::parse($r->date_fin) : $debut;
        $periode = $debut->isSameDay($fin)
            ? 'Le ' . $debut->format('d/m/Y')
            : 'Du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
        $heures = ($r->heure_debut && $r->heure_fin)
            ? substr($r->heure_debut, 0, 5) . ' - ' . substr($r->heure_fin, 0, 5)
            : 'Journée entière';
        return ['periode' => $periode, 'heures' => $heures];
    };

    $libellesStatut = ['disponible' => 'Disponible', 'indisponible' => 'Indisponible', 'maintenance' => 'Maintenance'];
@endphp

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .page-header h2 {
        color: #1A8969;
        font-size: 20px;
    }

    .svg { width: 16px; height: 16px; flex-shrink: 0; }
    .page-header .sous-titre { color: #888; font-size: 13px; margin-top: 3px; }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 9px 18px;
        border-radius: 20px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
        transition: 0.2s ease;
    }

    .btn-add {
        background: #1A8969;
        color: white;
        box-shadow: 0 2px 8px rgba(26, 137, 105, 0.3);
    }
    .btn-add:hover {
        background: #146e54;
        box-shadow: 0 4px 12px rgba(26, 137, 105, 0.4);
        transform: translateY(-1px);
    }

    .barre-controles {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 10px; flex-wrap: wrap; gap: 10px;
    }
    .barre-controles select, .barre-controles input {
        padding: 7px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; background: white;
    }
    .controle-gauche, .controle-droite { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; }

    .btn-export {
        display: inline-flex; align-items: center; gap: 7px;
        background: white; color: #1A8969;
        border: 1.3px solid #1A8969; border-radius: 20px;
        padding: 7px 16px; font-size: 12.5px; font-weight: 700; font-family: inherit;
        cursor: pointer; transition: 0.2s ease; white-space: nowrap;
    }
    .btn-export .svg { width: 15px; height: 15px; }
    .btn-export:hover { background: #1A8969; color: white; }

    .table-container {
        background: white;
        padding: 20px;
        border-radius: 12px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #eef1ef;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        text-align: left;
        font-size: 13px;
    }

    th {
        background: #1A8969;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.3px;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }
    th.tri-asc::after { content: " ▲"; font-size: 9px; }
    th.tri-desc::after { content: " ▼"; font-size: 9px; }
    th.non-triable { cursor: default; }

    tbody tr:hover { background: #f7faf8; }

    .statut {
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-block;
    }

    .disponible { background: #e8f5e9; color: #1A8969; }
    .indisponible { background: #fdeaea; color: #d9534f; }
    .maintenance { background: #fdf3d9; color: #b8860b; }

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
        transition: 0.2s ease;
        white-space: nowrap;
    }
    .btn-actions .svg { width: 14px; height: 14px; }
    .btn-actions:hover {
        background: #146e54;
        box-shadow: 0 3px 10px rgba(26, 137, 105, 0.3);
    }

    .menu-actions {
        display: none;
        position: fixed;
        background: white;
        border-radius: 10px;
        border: 1px solid #eef1ef;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        min-width: 170px;
        z-index: 900;
        overflow: hidden;
        padding: 6px 0;
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
        text-align: left;
        text-decoration: none;
        font-family: inherit;
        transition: 0.15s ease;
    }
    .menu-actions .item-action:hover { background: #f5f7f6; }
    .menu-actions .item-action .svg { width: 15px; height: 15px; }

    .item-consulter { color: #1565c0; }
    .item-modifier { color: #b8860b; }
    .item-supprimer { color: #d9534f; }
    .menu-actions .separateur { height: 1px; background: #eee; margin: 4px 0; }

    .message {
        padding: 12px 16px;
        margin-bottom: 15px;
        border-radius: 8px;
        background: #e8f5e9;
        color: #1A8969;
        font-size: 14px;
    }

    .error-message {
        padding: 12px 16px;
        margin-bottom: 15px;
        border-radius: 8px;
        background: #fdeaea;
        color: #d9534f;
        font-size: 14px;
    }

    .empty { text-align: center; color: #999; padding: 30px; }

    .pagination-zone {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 14px; font-size: 13px; color: #555; flex-wrap: wrap; gap: 10px;
    }
    .pagination-boutons button {
        padding: 6px 12px; margin-left: 4px; border: 1px solid #ccc; background: white;
        border-radius: 6px; cursor: pointer; font-size: 12px;
    }
    .pagination-boutons button.actif { background: #1A8969; color: white; border-color: #1A8969; }
    .pagination-boutons button:disabled { opacity: 0.4; cursor: default; }

    /* ===== MODALES ===== */
    .modale-fond {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 16px;
        pointer-events: auto;
    }
    .modale-fond.ouverte { display: flex; }

    .modale-boite {
        background: white;
        border-radius: 12px;
        padding: 28px 30px;
        width: 380px;
        max-width: 100%;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparition 0.2s ease;
    }
    @keyframes apparition {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modale-icone {
        width: 54px; height: 54px;
        border-radius: 50%;
        background: #fdeaea;
        color: #d9534f;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
    }

    .modale-icone .svg { width: 24px; height: 24px; }

    .modale-titre {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .modale-texte {
        font-size: 13.5px;
        color: #666;
        margin-bottom: 22px;
        line-height: 1.5;
    }
    .modale-texte strong { color: #1a1a1a; }

    .modale-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .modale-actions button {
        flex: 1;
        padding: 10px 16px;
        border-radius: 8px;
        border: none;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .modale-annuler { background: #f0f0f0; color: #555; }
    .modale-annuler:hover { background: #e2e2e2; }

    .modale-confirmer { background: #d9534f; color: white; }
    .modale-confirmer:hover { background: #c9302c; }

    /* ----- Modale consulter ----- */
    .modale-consulter-boite {
        background: white;
        border-radius: 14px;
        max-width: 560px;
        width: 100%;
        max-height: 92vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparition 0.2s ease;
    }
    .consulter-photo {
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
        border-radius: 14px 14px 0 0;
    }
    .consulter-photo-vide {
        width: 100%;
        height: 120px;
        background: #eef3f0;
        color: #9bb5a6;
        font-size: 13px;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 14px 14px 0 0;
    }
    .modale-consulter-entete {
        padding: 18px 24px 14px;
        border-bottom: 1px solid #eef1ef;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }
    .modale-consulter-nom { font-size: 19px; font-weight: 700; color: #1A8969; word-break: break-word; }
    .modale-consulter-ref { font-size: 12.5px; color: #999; margin-top: 3px; }
    .modale-consulter-corps { padding: 18px 24px 24px; }

    .consulter-infos {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 18px;
    }
    .consulter-info {
        display: flex; align-items: center; gap: 11px;
        padding: 10px 12px; background: #f7faf9; border: 1px solid #eef1ef; border-radius: 10px;
    }
    .consulter-info.pleine { grid-column: 1 / -1; }
    .consulter-info .icone {
        width: 34px; height: 34px; border-radius: 9px; background: #e6f5ef; color: #1A8969;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .consulter-info small { display: block; font-size: 11px; color: #888; margin-bottom: 1px; }
    .consulter-info strong { font-size: 13.5px; color: #222; word-break: break-word; }

    .consulter-section { margin-bottom: 16px; }
    .consulter-section h4 {
        display: flex; align-items: center; gap: 7px;
        font-size: 13px; color: #333; margin-bottom: 9px;
    }
    .consulter-section h4 .svg { color: #1A8969; width: 16px; height: 16px; }

    .puces { display: flex; flex-wrap: wrap; gap: 7px; }
    .puce { padding: 5px 12px; background: #e6f5ef; color: #146e54; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .texte-vide { font-size: 12.5px; color: #999; }

    .liste-occ { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 7px; max-height: 150px; overflow-y: auto; }
    .occ { padding: 8px 12px; border-radius: 9px; background: #fff4e0; border-left: 4px solid #e0a030; }
    .occ strong { display: block; font-size: 12.5px; color: #7a5200; }
    .occ span { font-size: 11.5px; color: #9a7a30; }
    .libre { padding: 9px 12px; border-radius: 9px; background: #e6f5ef; color: #146e54; font-size: 12.5px; font-weight: 600; border-left: 4px solid #1A8969; }

    .consulter-dates { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; font-size: 11.5px; color: #999; padding-top: 12px; border-top: 1px solid #eef1ef; }

    .modale-consulter-fermer {
        margin-top: 18px;
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 20px;
        background: #1A8969;
        color: white;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }
    .modale-consulter-fermer:hover { background: #146e54; }

    @media (max-width: 600px) {
        .page-header h2 { font-size: 17px; }
        .table-container { padding: 12px; }
        th, td { padding: 9px 10px; font-size: 12px; }
        .btn-add { width: 100%; justify-content: center; }
        .consulter-infos { grid-template-columns: 1fr; }
        .modale-consulter-entete { flex-direction: column; }
    }
</style>

<div class="page-header">
    <div>
        <h2>Gestion des salles</h2>
        <div class="sous-titre">{{ $salles->count() }} salle(s) enregistrée(s)</div>
    </div>

    <a href="{{ route('admin.salles.create') }}" class="btn btn-add">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter une salle
    </a>
</div>

@if (session('success'))
    <div class="message">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="error-message">{{ session('error') }}</div>
@endif

<div class="barre-controles">
    <div class="controle-gauche">
        Afficher
        <select id="parPage">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        salles
    </div>
    <div class="controle-droite">
        @if ($salles->count() > 0)
            <button type="button" id="btnExport" class="btn-export">
                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13l4 4M13 13l-4 4"/></svg>
                Exporter en Excel
            </button>
        @endif
        Rechercher :
        <input type="text" id="rechercheTable" placeholder="Nom, direction, localité...">
    </div>
</div>

<div class="table-container">

    @if ($salles->count() > 0)

        <table id="tableSalles">
            <thead>
                <tr>
                    <th data-col="0">Référence</th>
                    <th data-col="1">Nom</th>
                    <th data-col="2">Direction</th>
                    <th data-col="3">Localité</th>
                    <th data-col="4">Capacité</th>
                    <th data-col="5">Statut</th>
                    <th class="non-triable">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($salles as $salle)
                    @php
                        $listeEquip = collect(preg_split('/[,;\n]+/', (string) $salle->equipement))
                            ->map(fn ($e) => trim($e))->filter()->values()->all();
                        $occSalle = ($occupations[$salle->id] ?? collect())->map($libelleOccupation)->values()->all();
                        $nomDirection = $salle->direction->nom_direction ?? '-';
                        $texteCapacite = $salle->capacite ? $salle->capacite . ' places' : '-';
                    @endphp
                    <tr>
                        <td style="color:#999; font-weight:600; white-space:nowrap;">{{ $salle->reference }}</td>
                        <td>{{ $salle->nom }}</td>
                        <td>{{ $nomDirection }}</td>
                        <td>{{ $salle->localite ?? '-' }}</td>
                        <td>{{ $texteCapacite }}</td>
                        <td>
                            <span class="statut {{ $salle->statut }}">{{ $libellesStatut[$salle->statut] ?? ucfirst($salle->statut) }}</span>
                        </td>

                        <td>
                            <form id="formSuppression{{ $salle->id }}" method="POST"
                                  action="{{ route('admin.salles.destroy', $salle) }}">
                                @csrf
                                @method('DELETE')
                            </form>

                            <div class="actions-dropdown">
                                <button type="button" class="btn-actions" onclick="basculerMenu(this)">Actions <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>

                                <div class="menu-actions">
                                    <button type="button"
                                            class="item-action item-consulter"
                                            data-reference="{{ $salle->reference }}"
                                            data-nom="{{ $salle->nom }}"
                                            data-direction="{{ $nomDirection }}"
                                            data-localite="{{ $salle->localite ?? '-' }}"
                                            data-capacite="{{ $texteCapacite }}"
                                            data-equipements="{{ json_encode($listeEquip, JSON_UNESCAPED_UNICODE) }}"
                                            data-occupations="{{ json_encode($occSalle, JSON_UNESCAPED_UNICODE) }}"
                                            data-statut="{{ $salle->statut }}"
                                            data-photo="{{ $salle->photo ? asset('storage/' . $salle->photo) : '' }}"
                                            data-cree="{{ $salle->created_at ? $salle->created_at->format('d/m/Y') : '-' }}"
                                            data-modifie="{{ $salle->updated_at ? $salle->updated_at->format('d/m/Y') : '-' }}"
                                            data-nb-reservations="{{ $totalReservations[$salle->id] ?? 0 }}"
                                            onclick="ouvrirModaleConsulter(this)">
                                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg> Consulter
                                    </button>

                                    <a href="{{ route('admin.salles.edit', $salle) }}" class="item-action item-modifier">
                                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg> Modifier
                                    </a>

                                    <div class="separateur"></div>

                                    <button type="button"
                                            class="item-action item-supprimer"
                                            onclick="ouvrirModaleSuppression('{{ $salle->id }}', @js($salle->nom))">
                                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg> Supprimer
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-zone">
            <div id="infoPagination"></div>
            <div class="pagination-boutons" id="boutonsPagination"></div>
        </div>

    @else

        <p class="empty">Aucune salle n'est enregistrée pour le moment.</p>

    @endif

</div>

<!-- Modale consulter une salle -->
<div id="modaleConsulter" class="modale-fond">
    <div class="modale-consulter-boite">

        <img id="consulterPhoto" class="consulter-photo" alt="Photo de la salle" style="display:none;">
        <div id="consulterPhotoVide" class="consulter-photo-vide">Aucune photo</div>

        <div class="modale-consulter-entete">
            <div>
                <div class="modale-consulter-nom" id="consulterNom"></div>
                <div class="modale-consulter-ref" id="consulterRef"></div>
            </div>
            <span id="consulterStatut" class="statut"></span>
        </div>

        <div class="modale-consulter-corps">

            <div class="consulter-infos">
                <div class="consulter-info pleine">
                    <div class="icone">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
                    </div>
                    <div><small>Direction</small><strong id="consulterDirection"></strong></div>
                </div>
                <div class="consulter-info">
                    <div class="icone">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div><small>Ville</small><strong id="consulterLocalite"></strong></div>
                </div>
                <div class="consulter-info">
                    <div class="icone">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div><small>Capacité</small><strong id="consulterCapacite"></strong></div>
                </div>
                <div class="consulter-info pleine">
                    <div class="icone">
                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div><small>Réservations enregistrées</small><strong id="consulterNbReservations"></strong></div>
                </div>
            </div>

            <div class="consulter-section">
                <h4>
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Équipements
                </h4>
                <div id="consulterEquipements"></div>
            </div>

            <div class="consulter-section">
                <h4>
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Occupation à venir
                </h4>
                <div id="consulterOccupations"></div>
            </div>

            <div class="consulter-dates">
                <span>Créée le <span id="consulterCree"></span></span>
                <span>Modifiée le <span id="consulterModifie"></span></span>
            </div>

            <button type="button" class="modale-consulter-fermer" onclick="fermerModaleConsulter()">Fermer</button>
        </div>
    </div>
</div>

<!-- Modale de confirmation de suppression -->
<div id="modaleSuppression" class="modale-fond">
    <div class="modale-boite">
        <div class="modale-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></div>
        <div class="modale-titre">Supprimer cette salle ?</div>
        <div class="modale-texte">
            Vous êtes sur le point de supprimer <strong id="nomSalleASupprimer"></strong>.
            Cette action est irréversible.
        </div>
        <div class="modale-actions">
            <button type="button" class="modale-annuler" onclick="fermerModaleSuppression()">Annuler</button>
            <button type="button" class="modale-confirmer" onclick="confirmerSuppression()">Supprimer</button>
        </div>
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

/* ----- Tableau : recherche, tri, pagination ----- */
const table = document.getElementById('tableSalles');
if (table) {
    const tbody = table.querySelector('tbody');
    let lignes = Array.from(tbody.querySelectorAll('tr'));
    let pageActuelle = 1;
    let colonneTri = null;
    let sensTri = 1;

    // La colonne Actions n'entre pas dans la recherche
    function texteRecherche(tr) {
        return Array.from(tr.children).slice(0, -1)
            .map(function (td) { return td.textContent; })
            .join(' ')
            .toLowerCase();
    }

    function appliquerFiltre() {
        const recherche = document.getElementById('rechercheTable').value.toLowerCase().trim();
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
            'Affichage de ' + (debut + 1) + ' à ' + Math.min(debut + parPage, filtrees.length) + ' sur ' + filtrees.length + ' salles';

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
    // Export Excel : liste filtrée et triée, toutes pages confondues
    document.getElementById('btnExport').addEventListener('click', function () {
        const entetes = Array.from(table.querySelectorAll('thead th')).slice(0, -1)
            .map(function (th) { return th.textContent.trim(); });
        const donnees = appliquerFiltre().map(function (tr) {
            return Array.from(tr.children).slice(0, -1)
                .map(function (td) { return td.textContent.replace(/\s+/g, ' ').trim(); });
        });
        exporterVersExcel('Salles', entetes, donnees);
    });

    afficherPage();
}

/* ----- Export Excel : envoie le tableau affiché au serveur ----- */
function exporterVersExcel(titre, entetes, lignes) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('export.tableau') }}";
    form.style.display = 'none';

    const champs = {
        _token: "{{ csrf_token() }}",
        titre: titre,
        entetes: JSON.stringify(entetes),
        lignes: JSON.stringify(lignes)
    };
    Object.keys(champs).forEach(function (nom) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nom;
        input.value = champs[nom];
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    setTimeout(function () { form.remove(); }, 1500);
}

/* ----- Modale consulter ----- */
const libellesStatut = { disponible: 'Disponible', indisponible: 'Indisponible', maintenance: 'Maintenance' };

function lireJson(texte) {
    try { return JSON.parse(texte || '[]'); } catch (e) { return []; }
}

function ouvrirModaleConsulter(bouton) {
    fermerTousLesMenus();
    const d = bouton.dataset;

    document.getElementById('consulterNom').textContent = d.nom;
    document.getElementById('consulterRef').textContent = 'Réf. ' + d.reference;
    document.getElementById('consulterDirection').textContent = d.direction;
    document.getElementById('consulterLocalite').textContent = d.localite;
    document.getElementById('consulterCapacite').textContent = d.capacite;
    document.getElementById('consulterCree').textContent = d.cree;
    document.getElementById('consulterModifie').textContent = d.modifie;
    document.getElementById('consulterNbReservations').textContent = d.nbReservations;

    const statut = document.getElementById('consulterStatut');
    statut.className = 'statut ' + d.statut;
    statut.textContent = libellesStatut[d.statut] || d.statut;

    // Équipements en puces
    const zoneEquip = document.getElementById('consulterEquipements');
    zoneEquip.innerHTML = '';
    const equipements = lireJson(d.equipements);
    if (equipements.length) {
        const puces = document.createElement('div');
        puces.className = 'puces';
        equipements.forEach(function (e) {
            const s = document.createElement('span');
            s.className = 'puce';
            s.textContent = e;
            puces.appendChild(s);
        });
        zoneEquip.appendChild(puces);
    } else {
        zoneEquip.innerHTML = '<span class="texte-vide">Aucun équipement renseigné.</span>';
    }

    // Occupation à venir
    const zoneOcc = document.getElementById('consulterOccupations');
    zoneOcc.innerHTML = '';
    const occupations = lireJson(d.occupations);
    if (occupations.length) {
        const ul = document.createElement('ul');
        ul.className = 'liste-occ';
        occupations.forEach(function (o) {
            const li = document.createElement('li');
            li.className = 'occ';
            const p = document.createElement('strong');
            p.textContent = o.periode;
            const h = document.createElement('span');
            h.textContent = o.heures;
            li.appendChild(p);
            li.appendChild(h);
            ul.appendChild(li);
        });
        zoneOcc.appendChild(ul);
    } else {
        zoneOcc.innerHTML = '<div class="libre">Aucune réservation confirmée à venir : la salle est libre.</div>';
    }

    // Photo
    const photo = document.getElementById('consulterPhoto');
    const vide = document.getElementById('consulterPhotoVide');
    if (d.photo) {
        photo.onerror = function () { photo.style.display = 'none'; vide.style.display = 'flex'; };
        photo.src = d.photo;
        photo.style.display = 'block';
        vide.style.display = 'none';
    } else {
        photo.style.display = 'none';
        vide.style.display = 'flex';
    }

    document.getElementById('modaleConsulter').classList.add('ouverte');
}

function fermerModaleConsulter() {
    document.getElementById('modaleConsulter').classList.remove('ouverte');
}

document.getElementById('modaleConsulter').addEventListener('click', function (e) {
    if (e.target === this) fermerModaleConsulter();
});

/* ----- Modale de suppression ----- */
let idSalleASupprimer = null;

function ouvrirModaleSuppression(id, nom) {
    fermerTousLesMenus();
    idSalleASupprimer = id;
    document.getElementById('nomSalleASupprimer').textContent = nom;
    document.getElementById('modaleSuppression').classList.add('ouverte');
}

function fermerModaleSuppression() {
    idSalleASupprimer = null;
    document.getElementById('modaleSuppression').classList.remove('ouverte');
}

function confirmerSuppression() {
    if (idSalleASupprimer) {
        document.getElementById('formSuppression' + idSalleASupprimer).submit();
    }
}

document.getElementById('modaleSuppression').addEventListener('click', function (e) {
    if (e.target === this) fermerModaleSuppression();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        fermerTousLesMenus();
        fermerModaleConsulter();
        fermerModaleSuppression();
    }
});
</script>

@endsection