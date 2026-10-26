@extends('layouts.dgi')

@section('title', 'Gestion des utilisateurs - DGI')

@section('content')

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
        border-radius: 14px;
        overflow-x: auto;
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

    .user-nom {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #222;
    }

    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .role {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        display: inline-block;
    }

    .agent { background: #e8f5e9; color: #1A8969; }
    .gestionnaire { background: #e3f2fd; color: #1565c0; }
    .admin { background: #fdeaea; color: #d9534f; }

    .badge-expire {
        display: inline-block;
        margin-left: 6px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: #fff8e1;
        color: #b8941f;
        border: 1px solid #ecd58a;
        white-space: nowrap;
    }

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

    .menu-actions form { margin: 0; }

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

    .menu-actions .item-consulter { color: #1565c0; }
    .menu-actions .item-modifier { color: #b8860b; }
    .menu-actions .item-reactiver { color: #1A8969; }
    .menu-actions .item-supprimer { color: #d9534f; }
    .menu-actions .separateur {
        height: 1px;
        background: #eee;
        margin: 4px 0;
    }

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

    /* ===== Modales (suppression + consultation) ===== */
    .modale-fond-suppr,
    .modale-fond-consulter {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        pointer-events: auto;
    }
    .modale-fond-suppr.ouverte,
    .modale-fond-consulter.ouverte {
        display: flex;
    }

    .modale-suppr-boite {
        background: white;
        border-radius: 14px;
        max-width: 380px;
        width: 100%;
        padding: 26px 28px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparitionModale 0.2s ease;
    }
    @keyframes apparitionModale {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modale-suppr-icone .svg { width: 22px; height: 22px; }
    .modale-suppr-icone {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #fdeaea;
        color: #d9534f;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
    }
    .modale-suppr-boite h3 {
        margin: 0 0 8px;
        color: #1a1a1a;
        font-size: 17px;
    }
    .modale-suppr-boite p {
        margin: 0 0 22px;
        color: #777;
        font-size: 13.5px;
        line-height: 1.5;
    }
    .modale-suppr-boutons {
        display: flex;
        gap: 10px;
    }
    .modale-suppr-boutons button {
        flex: 1;
        padding: 9px 0;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: 0.2s ease;
    }
    .btn-suppr-annuler {
        background: white;
        color: #777;
        border: 1.3px solid #ccc;
    }
    .btn-suppr-annuler:hover {
        background: #f5f5f5;
        border-color: #999;
    }
    .btn-suppr-confirmer {
        background: #d9534f;
        color: white;
        border: 1.3px solid #d9534f;
    }
    .btn-suppr-confirmer:hover {
        background: #c0392b;
        border-color: #c0392b;
    }

    /* ----- Modale consulter ----- */
    .modale-consulter-boite {
        background: white;
        border-radius: 14px;
        max-width: 480px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        animation: apparitionModale 0.2s ease;
    }
    .modale-consulter-entete {
        background: #1A8969;
        color: white;
        padding: 24px;
        border-radius: 14px 14px 0 0;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .modale-consulter-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .modale-consulter-nom {
        font-size: 18px;
        font-weight: 700;
        word-break: break-word;
    }
    .modale-consulter-ref {
        font-size: 12.5px;
        opacity: 0.85;
        margin-top: 3px;
    }
    .modale-consulter-corps {
        padding: 22px 26px 26px;
    }
    .modale-consulter-grille {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px 18px;
    }
    .modale-consulter-champ .libelle {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 3px;
    }
    .modale-consulter-champ .valeur {
        font-size: 13.5px;
        color: #222;
        font-weight: 500;
        word-break: break-word;
    }
    .modale-consulter-pleine { grid-column: 1 / -1; }
    .modale-consulter-fermer {
        margin-top: 22px;
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 20px;
        background: #1A8969;
        color: white;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: 0.2s ease;
    }
    .modale-consulter-fermer:hover { background: #146e54; }

    @media (max-width: 500px) {
        .modale-consulter-grille { grid-template-columns: 1fr; }
    }
</style>

<div class="page-header">
    <div>
        <h2>Gestion des utilisateurs</h2>
        <div class="sous-titre">{{ $utilisateurs->count() }} compte(s) enregistré(s)</div>
    </div>

    <a href="{{ route('admin.users.create') }}" class="btn btn-add">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter un utilisateur
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
        utilisateurs
    </div>
    <div class="controle-droite">
        @if ($utilisateurs->count() > 0)
            <button type="button" id="btnExport" class="btn-export">
                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13l4 4M13 13l-4 4"/></svg>
                Exporter en Excel
            </button>
        @endif
        Rechercher :
        <input type="text" id="rechercheTable" placeholder="Nom, email, direction...">
    </div>
</div>

<div class="table-container">

    @if ($utilisateurs->count() > 0)

        <table id="tableUtilisateurs">
            <thead>
                <tr>
                    <th data-col="0">Référence</th>
                    <th data-col="1">Nom et prénom</th>
                    <th data-col="2">Email</th>
                    <th data-col="3">Téléphone</th>
                    <th data-col="4">Direction</th>
                    <th data-col="5">Service</th>
                    <th data-col="6">Rôle</th>
                    <th class="non-triable">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($utilisateurs as $utilisateur)
                    @php
                        $libelleRole = match ($utilisateur->role) {
                            'agent' => 'Agent demandeur',
                            'gestionnaire' => 'Gestionnaire',
                            'admin' => $utilisateur->est_principal ? 'Admin système' : 'Admin',
                            default => '-',
                        };
                        $initiale = mb_strtoupper(mb_substr($utilisateur->name, 0, 1));
                        $expire = $utilisateur->compteExpire();
                    @endphp
                    <tr>
                        <td>{{ $utilisateur->reference }}</td>
                        <td>
                            <div class="user-nom">
                                <div class="avatar" style="background: {{ ['#1A8969','#1565c0','#b8860b','#8e44ad','#d9534f'][crc32($utilisateur->name) % 5] }}">
                                    {{ $initiale }}
                                </div>
                                {{ $utilisateur->name }}
                            </div>
                        </td>
                        <td>{{ $utilisateur->email }}</td>
                        <td>{{ $utilisateur->telephone ?? '-' }}</td>
                        <td>{{ $utilisateur->direction->nom_direction ?? '-' }}</td>
                        <td>{{ $utilisateur->service->nom_service ?? '-' }}</td>
                        <td>
                            @if (in_array($utilisateur->role, ['agent', 'gestionnaire', 'admin'], true))
                                <span class="role {{ $utilisateur->role }}">{{ $libelleRole }}</span>
                            @else
                                -
                            @endif
                            @if ($expire)
                                <span class="badge-expire" title="Compte jamais utilisé depuis plus de 3 jours">Expiré</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-dropdown">
                                <button type="button" class="btn-actions" onclick="basculerMenu(this)">Actions <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>

                                <div class="menu-actions">
                                    <button type="button"
                                            class="item-action item-consulter"
                                            data-reference="{{ $utilisateur->reference }}"
                                            data-nom="{{ $utilisateur->name }}"
                                            data-email="{{ $utilisateur->email }}"
                                            data-telephone="{{ $utilisateur->telephone ?? '-' }}"
                                            data-direction="{{ $utilisateur->direction->nom_direction ?? '-' }}"
                                            data-service="{{ $utilisateur->service->nom_service ?? '-' }}"
                                            data-role="{{ $libelleRole }}"
                                            data-cree="{{ $utilisateur->created_at ? $utilisateur->created_at->format('d/m/Y') : '-' }}"
                                            data-derniere-connexion="{{ $utilisateur->last_login_at ? $utilisateur->last_login_at->format('d/m/Y à H:i') : ($expire ? 'Jamais connecté (compte expiré)' : 'Jamais connecté') }}"
                                            data-nb-connexions="{{ $utilisateur->login_count ?? 0 }}"
                                            data-nb-demandes="{{ $utilisateur->reservations_count ?? $utilisateur->reservations()->count() }}"
                                            onclick="ouvrirModaleConsulter(this)">
                                        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg> Consulter
                                    </button>

                                    @if (auth()->user()->peutGerer($utilisateur))
                                        <a href="{{ route('admin.users.edit', $utilisateur) }}" class="item-action item-modifier">
                                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg> Modifier
                                        </a>

                                        @if ($expire)
                                            <form method="POST" action="{{ route('admin.users.reactiver', $utilisateur) }}">
                                                @csrf
                                                <button type="submit" class="item-action item-reactiver">
                                                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/></svg> Réactiver
                                                </button>
                                            </form>
                                        @endif

                                        <div class="separateur"></div>

                                        <button type="button"
                                                class="item-action item-supprimer"
                                                onclick="ouvrirModaleSuppression('{{ route('admin.users.destroy', $utilisateur) }}', @js($utilisateur->name))">
                                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg> Supprimer
                                        </button>
                                    @endif
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

        <p class="empty">Aucun utilisateur n'est enregistré pour le moment.</p>

    @endif

</div>

<!-- Modale consulter un utilisateur -->
<div class="modale-fond-consulter" id="modaleConsulter">
    <div class="modale-consulter-boite">
        <div class="modale-consulter-entete">
            <div class="modale-consulter-avatar" id="consulterAvatar"></div>
            <div>
                <div class="modale-consulter-nom" id="consulterNom"></div>
                <div class="modale-consulter-ref" id="consulterRef"></div>
            </div>
        </div>

        <div class="modale-consulter-corps">
            <div class="modale-consulter-grille">
                <div class="modale-consulter-champ modale-consulter-pleine">
                    <div class="libelle">Email</div>
                    <div class="valeur" id="consulterEmail"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Téléphone</div>
                    <div class="valeur" id="consulterTelephone"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Rôle</div>
                    <div class="valeur" id="consulterRole"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Direction</div>
                    <div class="valeur" id="consulterDirection"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Service</div>
                    <div class="valeur" id="consulterService"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Compte créé le</div>
                    <div class="valeur" id="consulterCree"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Demandes effectuées</div>
                    <div class="valeur" id="consulterNbDemandes"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Dernière connexion</div>
                    <div class="valeur" id="consulterDerniereConnexion"></div>
                </div>
                <div class="modale-consulter-champ">
                    <div class="libelle">Nombre de connexions</div>
                    <div class="valeur" id="consulterNbConnexions"></div>
                </div>
            </div>

            <button type="button" class="modale-consulter-fermer" onclick="fermerModaleConsulter()">Fermer</button>
        </div>
    </div>
</div>

<!-- Modale de confirmation de suppression -->
<div class="modale-fond-suppr" id="modaleSuppression">
    <div class="modale-suppr-boite">
        <div class="modale-suppr-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></div>
        <h3>Supprimer cet utilisateur ?</h3>
        <p id="texteSuppression">Cette action est irréversible. Voulez-vous vraiment continuer ?</p>

        <form id="formSuppression" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modale-suppr-boutons">
                <button type="button" class="btn-suppr-annuler" onclick="fermerModaleSuppression()">Annuler</button>
                <button type="submit" class="btn-suppr-confirmer">Supprimer</button>
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

    // Positionnement : sous le bouton, ou au-dessus s'il n'y a pas la place
    const r = bouton.getBoundingClientRect();
    const h = menu.offsetHeight;
    const w = menu.offsetWidth;

    let top = r.bottom + 6;
    if (top + h > window.innerHeight - 8) {
        top = r.top - h - 6;
    }
    let left = r.right - w;
    if (left < 8) left = 8;

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.actions-dropdown')) {
        fermerTousLesMenus();
    }
});
window.addEventListener('scroll', fermerTousLesMenus, true);
window.addEventListener('resize', fermerTousLesMenus);

/* ----- Tableau : recherche, tri, pagination ----- */
const table = document.getElementById('tableUtilisateurs');
if (table) {
    const tbody = table.querySelector('tbody');
    let lignes = Array.from(tbody.querySelectorAll('tr'));
    let pageActuelle = 1;
    let colonneTri = null;
    let sensTri = 1;

    // Le texte du menu d'actions n'entre pas dans la recherche
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
            'Affichage de ' + (debut + 1) + ' à ' + Math.min(debut + parPage, filtrees.length) + ' sur ' + filtrees.length + ' utilisateurs';

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
        exporterVersExcel('Utilisateurs', entetes, donnees);
    });    

    afficherPage();
}

/* ----- Modale consulter ----- */
function ouvrirModaleConsulter(bouton) {
    fermerTousLesMenus();
    const d = bouton.dataset;
    document.getElementById('consulterAvatar').textContent = (d.nom || '?').charAt(0).toUpperCase();
    document.getElementById('consulterNom').textContent = d.nom;
    document.getElementById('consulterRef').textContent = d.reference;
    document.getElementById('consulterEmail').textContent = d.email;
    document.getElementById('consulterTelephone').textContent = d.telephone;
    document.getElementById('consulterRole').textContent = d.role;
    document.getElementById('consulterDirection').textContent = d.direction;
    document.getElementById('consulterService').textContent = d.service;
    document.getElementById('consulterCree').textContent = d.cree;
    document.getElementById('consulterNbDemandes').textContent = d.nbDemandes;
    document.getElementById('consulterDerniereConnexion').textContent = d.derniereConnexion;
    document.getElementById('consulterNbConnexions').textContent = d.nbConnexions;
    document.getElementById('modaleConsulter').classList.add('ouverte');
}

function fermerModaleConsulter() {
    document.getElementById('modaleConsulter').classList.remove('ouverte');
}

document.getElementById('modaleConsulter').addEventListener('click', function (e) {
    if (e.target === this) fermerModaleConsulter();
});

/* ----- Modale de suppression ----- */
function ouvrirModaleSuppression(url, nom) {
    fermerTousLesMenus();
    document.getElementById('formSuppression').action = url;
    document.getElementById('texteSuppression').textContent =
        'Voulez-vous vraiment supprimer ' + nom + ' ? Cette action est irréversible.';
    document.getElementById('modaleSuppression').classList.add('ouverte');
}

function fermerModaleSuppression() {
    document.getElementById('modaleSuppression').classList.remove('ouverte');
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