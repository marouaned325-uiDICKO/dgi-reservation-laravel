@extends('layouts.dgi')

@section('title', 'Gestion des services - DGI')

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
    .btn-add.desactive { background: #bbb; box-shadow: none; pointer-events: none; }

    .btn-lien {
        background: white;
        color: #1A8969;
        border: 1.3px solid #1A8969;
    }
    .btn-lien:hover { background: #1A8969; color: white; }
    .zone-boutons-entete { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

    .barre-controles {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }

    .afficher-zone {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #555;
    }

    .afficher-zone select {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 13px;
        background: white;
    }

    .zone-droite-controles { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

    .filtre-direction {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        background: white;
        max-width: 240px;
    }

    .recherche-services {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        max-width: 320px;
        width: 100%;
    }

    .recherche-services input {
        width: 100%;
        border: none;
        outline: none;
        font-size: 13px;
        background: transparent;
        font-family: inherit;
    }

    .message {
        padding: 12px 16px;
        margin-bottom: 15px;
        border-radius: 8px;
        background: #e8f5e9;
        color: #1A8969;
        font-size: 14px;
    }

    .message-erreur {
        padding: 12px 16px;
        margin-bottom: 15px;
        border-radius: 8px;
        background: #fdeaea;
        color: #d9534f;
        font-size: 14px;
    }
    .message-avertissement { background: #fff3cd; color: #856404; }
    .message-avertissement a { color: #856404; font-weight: 700; }

    .table-wrapper {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #eef1ef;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    thead tr {
        background: #1A8969;
    }

    thead th {
        color: white;
        text-align: left;
        padding: 13px 18px;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s ease;
    }
    tbody tr:last-child {
        border-bottom: none;
    }
    tbody tr:hover {
        background: #f7faf8;
    }

    tbody td {
        padding: 14px 18px;
        font-size: 13.5px;
        color: #333;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .col-service { width: 40%; }
    .col-direction { width: 35%; }
    .col-actions { width: 25%; white-space: nowrap; }
    td.col-service { font-weight: 700; color: #1A8969; }

    .service-tag {
        background: #e8f5e9;
        color: #1A8969;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }

    /* Liste d'actions (menu déroulant) */
    .actions-dropdown { display: inline-block; }
    .btn-actions {
        display: inline-flex; align-items: center; gap: 6px;
        background: #1A8969; color: white; border: none;
        padding: 7px 16px; border-radius: 20px; cursor: pointer;
        font-size: 12px; font-weight: 700; transition: 0.2s ease; white-space: nowrap;
    }
    .btn-actions:hover { background: #146e54; box-shadow: 0 3px 10px rgba(26, 137, 105, 0.3); }
    .btn-actions .svg { width: 14px; height: 14px; }
    .menu-actions {
        display: none; position: fixed; min-width: 170px; padding: 6px 0;
        background: white; border-radius: 10px; border: 1px solid #eef1ef;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18); z-index: 900; overflow: hidden;
    }
    .menu-actions.ouvert { display: block; }
    .menu-actions .item-action {
        display: flex; align-items: center; gap: 10px; width: 100%;
        padding: 10px 16px; border: none; background: none; cursor: pointer;
        font-size: 13px; font-weight: 600; font-family: inherit; text-align: left;
        text-decoration: none; transition: 0.15s ease;
    }
    .menu-actions .item-action:hover { background: #f5f7f6; }
    .menu-actions .ic {
        width: 22px; height: 22px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .menu-actions .ic .svg { width: 13px; height: 13px; }
    .item-modifier { color: #b8860b; }
    .item-modifier .ic { background: #fff6dd; }
    .item-supprimer { color: #d9534f; }
    .item-supprimer .ic { background: #fdeaea; }
    .menu-actions .item-supprimer:hover { background: #fff3f3; }
    .menu-actions .separateur { height: 1px; background: #eee; margin: 4px 0; }

    .empty {
        text-align: center;
        color: #999;
        padding: 30px;
    }

    .pied-tableau {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
        font-size: 13px;
        color: #666;
    }

    .pagination-zone {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pagination-zone button {
        border: 1px solid #ddd;
        background: white;
        color: #555;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12.5px;
        cursor: pointer;
        font-family: inherit;
        transition: 0.2s ease;
    }
    .pagination-zone button:hover:not(:disabled) {
        background: #f3f9f5;
        border-color: #1A8969;
        color: #1A8969;
    }
    .pagination-zone button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pagination-zone .page-courante {
        background: #1A8969;
        color: white;
        border: none;
        padding: 6px 13px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
    }

    @media (max-width: 650px) {
        .table-wrapper { overflow-x: auto; }
        table { min-width: 560px; }
    }

    /* ===== Modale de suppression ===== */
    .modale-fond-suppr {
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
    .modale-fond-suppr.ouverte {
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
        animation: apparitionModaleSuppr 0.2s ease;
    }
    @keyframes apparitionModaleSuppr {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
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
    .modale-suppr-icone .svg { width: 22px; height: 22px; }
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
</style>

<div class="page-header">
    <div>
        <h2>Gestion des services</h2>
        <div class="sous-titre">{{ $services->count() }} service(s) enregistré(s)</div>
    </div>
    <div class="zone-boutons-entete">
        @if (auth()->user()->role === 'admin')
            <a href="{{ route('admin.directions.index') }}" class="btn btn-lien"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-5h6v5"/></svg> Voir les directions</a>
        @endif
        @if ($directions->isEmpty())
            <span class="btn btn-add desactive" title="Créez d'abord une direction"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter un service</span>
        @else
            <a href="{{ route('admin.services.create', $directionChoisie ? ['direction' => $directionChoisie] : []) }}" class="btn btn-add"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter un service</a>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="message">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="message-erreur">{{ session('error') }}</div>
@endif

@if ($directions->isEmpty())
    <div class="message-erreur message-avertissement">
        Aucune direction n'existe encore. Créez d'abord une direction pour pouvoir lui ajouter des services.
        @if (auth()->user()->role === 'admin')
            <a href="{{ route('admin.directions.create') }}">Créer une direction <svg class="svg" style="width:14px;height:14px;vertical-align:-2px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        @endif
    </div>
@endif

<div class="barre-controles">
    <div class="afficher-zone">
        Afficher
        <select id="parPage">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="999999">Tout</option>
        </select>
        services
    </div>

    <div class="zone-droite-controles">
        <select id="filtreDirection" class="filtre-direction">
            <option value="">Toutes les directions</option>
            @foreach ($directions as $dir)
                <option value="{{ $dir->id }}" @selected((string) $directionChoisie === (string) $dir->id)>{{ $dir->nom_direction }}</option>
            @endforeach
        </select>
        <div class="recherche-services">
            <input type="text" id="rechercheService" placeholder="Rechercher un service...">
        </div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th class="col-service">Service</th>
                <th class="col-direction">Direction</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody id="listeServices">
            @forelse ($services as $service)
            <tr class="ligne-service" data-direction="{{ $service->direction_id }}"
                data-nom="{{ mb_strtolower($service->nom_service . ' ' . ($service->direction->nom_direction ?? '')) }}">
                <td class="col-service">{{ $service->nom_service }}</td>
                <td class="col-direction">
                    <span class="service-tag">{{ $service->direction->nom_direction ?? 'Direction supprimée' }}</span>
                </td>
                <td class="col-actions">
                    <div class="actions-dropdown">
                        <button type="button" class="btn-actions" onclick="basculerMenu(this)">Actions <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>
                        <div class="menu-actions">
                            <a href="{{ route('admin.services.edit', $service) }}" class="item-action item-modifier">
                                <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></span> Modifier
                            </a>
                            <div class="separateur"></div>
                            <button type="button" class="item-action item-supprimer"
                                    onclick="ouvrirModaleSuppression('{{ route('admin.services.destroy', $service) }}', @js($service->nom_service))">
                                <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></span> Supprimer
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="empty">Aucun service enregistré.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pied-tableau">
    <div id="texteAffichage">Affichage de 0 à 0 sur 0 services</div>
    <div class="pagination-zone" id="zonePagination">
        <button type="button" id="btnPrecedent">Précédent</button>
        <span class="page-courante" id="pageCourante">1</span>
        <button type="button" id="btnSuivant">Suivant</button>
    </div>
</div>

<!-- Modale de confirmation de suppression -->
<div class="modale-fond-suppr" id="modaleSuppression">
    <div class="modale-suppr-boite">
        <div class="modale-suppr-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></div>
        <h3>Supprimer ce service ?</h3>
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
    document.querySelectorAll('.menu-actions.ouvert').forEach(function (m) { m.classList.remove('ouvert'); });
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

/* ----- Recherche, filtre et pagination ----- */
const selectParPage = document.getElementById('parPage');
const btnPrecedent = document.getElementById('btnPrecedent');
const btnSuivant = document.getElementById('btnSuivant');
const texteAffichage = document.getElementById('texteAffichage');
const pageCouranteEl = document.getElementById('pageCourante');

let page = 1;

function toutesLesLignes() {
    return Array.from(document.querySelectorAll('#listeServices .ligne-service'));
}

function paginer() {
    const parPage = parseInt(selectParPage.value, 10);
    const recherche = document.getElementById('rechercheService').value.toLowerCase();
    const direction = document.getElementById('filtreDirection').value;
    const lignes = toutesLesLignes().filter(function (l) {
        return l.dataset.nom.includes(recherche) && (direction === '' || l.dataset.direction === direction);
    });
    toutesLesLignes().forEach(function (l) { l.style.display = 'none'; });
    const total = lignes.length;
    const totalPages = Math.max(1, Math.ceil(total / parPage));

    if (page > totalPages) page = totalPages;
    if (page < 1) page = 1;

    const debut = (page - 1) * parPage;
    const fin = Math.min(debut + parPage, total);

    lignes.forEach(function (ligne, index) {
        ligne.style.display = (index >= debut && index < fin) ? '' : 'none';
    });

    texteAffichage.textContent = total === 0
        ? 'Affichage de 0 à 0 sur 0 services'
        : 'Affichage de ' + (debut + 1) + ' à ' + fin + ' sur ' + total + ' services';

    pageCouranteEl.textContent = page;
    btnPrecedent.disabled = page <= 1;
    btnSuivant.disabled = page >= totalPages;
}

document.getElementById('rechercheService').addEventListener('input', function () { page = 1; paginer(); });
document.getElementById('filtreDirection').addEventListener('change', function () { page = 1; paginer(); });
selectParPage.addEventListener('change', function () { page = 1; paginer(); });
btnPrecedent.addEventListener('click', function () { page--; paginer(); });
btnSuivant.addEventListener('click', function () { page++; paginer(); });

paginer();

/* ----- Modale de suppression ----- */
function ouvrirModaleSuppression(url, nom) {
    fermerTousLesMenus();
    document.getElementById('formSuppression').action = url;
    document.getElementById('texteSuppression').textContent =
        'Voulez-vous vraiment supprimer le service "' + nom + '" ? Cette action est irréversible.';
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
        fermerModaleSuppression();
    }
});
</script>
@endsection