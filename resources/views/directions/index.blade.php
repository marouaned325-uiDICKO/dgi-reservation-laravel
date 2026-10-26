@extends('layouts.dgi')

@section('title', 'Gestion des directions - DGI')

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

    .recherche-directions {
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

    .recherche-directions input {
        width: 100%;
        border: none;
        outline: none;
        font-size: 13px;
        background: transparent;
    }

    .message {
        padding: 12px 16px;
        margin-bottom: 15px;
        border-radius: 8px;
        background: #e8f5e9;
        color: #1A8969;
        font-size: 14px;
    }

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

    td.col-reference {
        width: 11%;
        font-weight: 700;
        color: #555;
        font-size: 12.5px;
        letter-spacing: 0.3px;
    }

    td.col-direction {
        font-weight: 700;
        color: #1A8969;
        width: 17%;
    }

    td.col-services {
        width: 55%;
    }

    td.col-actions {
        width: 17%;
        white-space: nowrap;
    }

    .services-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .service-tag {
        background: #e8f5e9;
        color: #1A8969;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }

    .direction-actions {
        display: flex;
        gap: 8px;
    }

    .message-erreur {
        padding: 12px 16px;
        margin-bottom: 15px;
        border-radius: 8px;
        background: #fdeaea;
        color: #d9534f;
        font-size: 14px;
    }

    .zone-boutons-entete { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

    .compteur-services {
        background: #e8f5e9;
        color: #1A8969;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: 0.2s ease;
    }
    .compteur-services:hover { background: #1A8969; color: white; }
    .compteur-services.vide { background: #fff3cd; color: #856404; }
    .compteur-services.vide:hover { background: #856404; color: white; }

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

    /* LISTE D'ACTIONS (menu déroulant) */
    .actions-dropdown { display: inline-block; }
    .btn-actions {
        display: inline-flex; align-items: center; gap: 6px;
        background: #1A8969; color: white; border: none;
        padding: 7px 16px; border-radius: 20px; cursor: pointer;
        font-size: 12px; font-weight: 700; transition: 0.2s ease; white-space: nowrap;
    }
    .btn-actions:hover { background: #146e54; box-shadow: 0 3px 10px rgba(26, 137, 105, 0.3); }
    .menu-actions {
        display: none; position: fixed; min-width: 190px; padding: 6px 0;
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
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .menu-actions .ic .svg { width: 13px; height: 13px; }
    .item-consulter { color: #1565c0; }
    .item-consulter .ic { background: #e3f2fd; }
    .item-modifier { color: #e6a700; }
    .item-modifier .ic { background: #fff6dd; }
    .item-supprimer { color: #d9534f; }
    .item-supprimer .ic { background: #fdeaea; }
    .menu-actions .item-supprimer:hover { background: #fff3f3; }
    .menu-actions .separateur { height: 1px; background: #eee; margin: 4px 0; }

    /* MODALE CONSULTATION */
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
    .modale-fond-consulter.ouverte { display: flex; }
    .modale-consulter-boite {
        background: white;
        border-radius: 14px;
        max-width: 460px;
        width: 100%;
        max-height: 85vh;
        overflow-y: auto;
        padding: 26px 28px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    }
    .modale-consulter-boite h3 {
        margin: 0 0 16px;
        color: #1A8969;
        font-size: 18px;
    }
    .info-ligne {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13.5px;
    }
    .info-ligne span:first-child { color: #888; }
    .info-ligne span:last-child { color: #222; font-weight: 600; text-align: right; }
    .consulter-sous-titre {
        margin: 16px 0 8px;
        font-size: 13px;
        font-weight: 700;
        color: #444;
    }
    .consulter-services { display: flex; flex-wrap: wrap; gap: 6px; }
    .btn-fermer-consulter {
        display: block;
        margin: 22px auto 0;
        padding: 9px 30px;
        border-radius: 20px;
        border: 1.3px solid #ccc;
        background: white;
        color: #777;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-fermer-consulter:hover { background: #f5f5f5; border-color: #999; }

    @media (max-width: 650px) {
        .table-wrapper { overflow-x: auto; }
        table { min-width: 560px; }
    }

    /* =========================
       MODALE SUPPRESSION
    ========================= */
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
        <h2>Gestion des directions</h2>
        <div class="sous-titre">{{ $directions->count() }} direction(s) enregistrée(s)</div>
    </div>
    <div class="zone-boutons-entete">
        <a href="{{ route('admin.directions.create') }}" class="btn btn-add"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter une direction</a>
    </div>
</div>

@if(session('success'))
    <div class="message">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="message-erreur">{{ session('error') }}</div>
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
        directions
    </div>

    <div class="recherche-directions">
        <input type="text" id="rechercheDirection" placeholder="Rechercher une direction ou un service...">
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th class="col-reference">Référence</th>
                <th class="col-direction">Direction</th>
                <th class="col-services">Services</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody id="listeDirections">
            @forelse ($directions as $direction)
            <tr class="ligne-direction" data-nom="{{ strtolower('DIR-' . str_pad($direction->id, 3, '0', STR_PAD_LEFT) . ' ' . $direction->nom_direction . ' ' . $direction->services->pluck('nom_service')->implode(' ')) }}">
                <td class="col-reference">DIR-{{ str_pad($direction->id, 3, '0', STR_PAD_LEFT) }}</td>
                <td class="col-direction">{{ $direction->nom_direction }}</td>
                <td class="col-services">
                    @if ($direction->services->isNotEmpty())
                        <div class="services-tags">
                            @foreach ($direction->services as $service)
                                <span class="service-tag">{{ $service->nom_service }}</span>
                            @endforeach
                        </div>
                    @else
                        <a href="{{ route('admin.services.create', ['direction' => $direction->id]) }}" class="compteur-services vide">
                            Aucun service : en ajouter
                        </a>
                    @endif
                </td>
                <td class="col-actions">
                    <div class="actions-dropdown">
                        <button type="button" class="btn-actions" onclick="basculerMenu(this)">Actions <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>
                        <div class="menu-actions">
                            <button type="button" class="item-action item-consulter"
                                    onclick="ouvrirModaleConsulter(@js(['reference' => 'DIR-' . str_pad($direction->id, 3, '0', STR_PAD_LEFT), 'nom' => $direction->nom_direction, 'services' => $direction->services->pluck('nom_service')]))">
                                <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></span> Consulter
                            </button>
                            <a href="{{ route('admin.directions.edit', $direction) }}" class="item-action item-modifier">
                                <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></span> Modifier
                            </a>
                            <div class="separateur"></div>
                            <button type="button" class="item-action item-supprimer"
                                    onclick="ouvrirModaleSuppression('{{ route('admin.directions.destroy', $direction) }}', @js($direction->nom_direction))">
                                <span class="ic"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></span> Supprimer
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="empty">Aucune direction enregistrée. Commencez par en ajouter une.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pied-tableau">
    <div id="texteAffichage">Affichage de 0 à 0 sur 0 directions</div>
    <div class="pagination-zone" id="zonePagination">
        <button id="btnPrecedent">Précédent</button>
        <span class="page-courante" id="pageCourante">1</span>
        <button id="btnSuivant">Suivant</button>
    </div>
</div>

<!-- MODALE CONSULTATION -->
<div class="modale-fond-consulter" id="modaleConsulter">
    <div class="modale-consulter-boite">
        <h3>Détails de la direction</h3>
        <div class="info-ligne"><span>Référence</span><span id="consRef"></span></div>
        <div class="info-ligne"><span>Direction</span><span id="consNom"></span></div>
        <div class="info-ligne"><span>Nombre de services</span><span id="consNb"></span></div>
        <div class="consulter-sous-titre">Services</div>
        <div class="consulter-services" id="consServices"></div>
        <button type="button" class="btn-fermer-consulter" onclick="fermerModaleConsulter()">Fermer</button>
    </div>
</div>

<!-- =========================
     MODALE DE CONFIRMATION SUPPRESSION
========================= -->

<div class="modale-fond-suppr" id="modaleSuppression">
    <div class="modale-suppr-boite">
        <div class="modale-suppr-icone"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></div>
        <h3>Supprimer cette direction ?</h3>
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

const selectParPage = document.getElementById('parPage');
const btnPrecedent = document.getElementById('btnPrecedent');
const btnSuivant = document.getElementById('btnSuivant');
const texteAffichage = document.getElementById('texteAffichage');
const pageCouranteEl = document.getElementById('pageCourante');
const champRecherche = document.getElementById('rechercheDirection');

let page = 1;

function toutesLesLignes() {
    return Array.from(document.querySelectorAll('#listeDirections .ligne-direction'));
}

function paginer() {
    const parPage = parseInt(selectParPage.value, 10);
    const lignes = toutesLesLignes().filter(l => l.dataset.nom.includes(champRecherche.value.toLowerCase()));
    toutesLesLignes().forEach(l => { l.style.display = 'none'; });
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
        ? 'Affichage de 0 à 0 sur 0 directions'
        : `Affichage de ${debut + 1} à ${fin} sur ${total} directions`;

    pageCouranteEl.textContent = page;
    btnPrecedent.disabled = page <= 1;
    btnSuivant.disabled = page >= totalPages;
}

champRecherche.addEventListener('input', function () {
    page = 1;
    paginer();
});

selectParPage.addEventListener('change', function () {
    page = 1;
    paginer();
});

btnPrecedent.addEventListener('click', function () {
    page--;
    paginer();
});

btnSuivant.addEventListener('click', function () {
    page++;
    paginer();
});

paginer();

function ouvrirModaleConsulter(d) {
    document.getElementById('consRef').textContent = d.reference;
    document.getElementById('consNom').textContent = d.nom;
    document.getElementById('consNb').textContent = d.services.length;
    const zone = document.getElementById('consServices');
    zone.innerHTML = '';
    if (d.services.length === 0) {
        zone.innerHTML = '<span style="color:#999;font-size:12.5px;">Aucun service pour le moment.</span>';
    } else {
        d.services.forEach(function (nom) {
            const s = document.createElement('span');
            s.className = 'service-tag';
            s.textContent = nom;
            zone.appendChild(s);
        });
    }
    document.getElementById('modaleConsulter').classList.add('ouverte');
}

function fermerModaleConsulter() {
    document.getElementById('modaleConsulter').classList.remove('ouverte');
}

document.getElementById('modaleConsulter').addEventListener('click', function (e) {
    if (e.target === this) {
        fermerModaleConsulter();
    }
});

function ouvrirModaleSuppression(url, nom) {
    document.getElementById('formSuppression').action = url;
    document.getElementById('texteSuppression').textContent =
        'Voulez-vous vraiment supprimer la direction "' + nom + '" et tous ses services ? Cette action est irréversible. Elle est refusée si des utilisateurs y sont rattachés.';
    document.getElementById('modaleSuppression').classList.add('ouverte');
}

function fermerModaleSuppression() {
    document.getElementById('modaleSuppression').classList.remove('ouverte');
}

document.getElementById('modaleSuppression').addEventListener('click', function (e) {
    if (e.target === this) {
        fermerModaleSuppression();
    }
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