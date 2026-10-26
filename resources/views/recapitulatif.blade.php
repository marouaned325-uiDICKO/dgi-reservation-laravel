@extends('layouts.dgi')

@section('title', 'Récapitulatif par direction - DGI')

@section('content')
<style>
    :root {
        --vert: #1A8969;
        --vert-fonce: #1A8969;
        --or: #D4AF37;
        --or-fonce: #b8941f;
        --or-pale: #fff8e1;
    }

    .svg { width: 16px; height: 16px; flex-shrink: 0; }

    .page-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 18px; flex-wrap: wrap; gap: 10px;
    }
    .page-header h2 { color: var(--vert); font-size: 20px; }
    .page-header h2::after {
        content: ''; display: block; width: 54px; height: 4px; border-radius: 4px; margin-top: 6px;
        background: linear-gradient(90deg, var(--vert) 0%, var(--vert) 55%, var(--or) 55%, var(--or) 100%);
    }
    .page-header .sous-titre { color: #888; font-size: 13px; margin-top: 8px; }

    .alerte-direction {
        display: flex; align-items: center; gap: 12px; padding: 13px 16px; margin-bottom: 18px;
        border-radius: 10px; font-size: 13.5px; font-weight: 600;
        background: var(--or-pale); color: #7a5d05; border: 1px solid #ecd58a;
        border-left: 5px solid var(--or);
    }
    .alerte-direction .svg { color: var(--or-fonce); }
    .alerte-direction a { color: #7a5d05; text-decoration: underline; }

    .resume { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
    .resume-carte {
        position: relative; background: white; border-radius: 12px; padding: 18px 18px 16px;
        border: 1px solid #eef1ef; overflow: hidden;
    }
    .resume-carte::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--vert) 0%, var(--vert) 60%, var(--or) 100%);
    }
    .resume-carte .valeur { font-size: 26px; font-weight: 800; color: #1a1a1a; line-height: 1.1; }
    .resume-carte .libelle { font-size: 12px; color: #777; margin-top: 4px; }

    .barre-controles { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
    .afficher { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; }
    .afficher select {
        padding: 8px 10px; border: 1px solid #ddd; border-radius: 10px; background: white;
        font-size: 13px; font-family: inherit; cursor: pointer;
    }
    .afficher select:focus { outline: none; border-color: var(--vert); }
    .recherche {
        display: flex; align-items: center; gap: 8px; background: white;
        border: 1px solid #ddd; border-radius: 20px; padding: 7px 14px; min-width: 260px;
    }
    .recherche:focus-within { border-color: var(--or); box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.22); }
    .recherche .svg { color: var(--or-fonce); }
    .recherche input { border: none; outline: none; font-size: 13px; width: 100%; font-family: inherit; background: transparent; }

    .table-container {
        background: white; border-radius: 12px; overflow-x: auto;
        -webkit-overflow-scrolling: touch; border: 1px solid #eef1ef;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 14px; border-bottom: 1px solid #f0f0f0; text-align: center; font-size: 13px; white-space: nowrap; }
    th:first-child, td:first-child { text-align: left; }

    thead tr.groupe th {
        background: var(--vert-fonce); color: white; font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.5px; border-bottom: 3px solid var(--or);
    }
    thead tr.colonnes th {
        background: var(--vert); color: white; font-weight: 600; font-size: 11px;
        text-transform: uppercase; letter-spacing: 0.3px; border-bottom: none;
    }
    thead th.sep { border-left: 1px solid rgba(255,255,255,0.22); }

    tbody tr:nth-child(even) { background: #fcfcfa; }
    tbody tr:hover { background: var(--or-pale); }
    tbody td.nom { font-weight: 700; color: #1a1a1a; border-left: 4px solid transparent; }
    tbody tr:hover td.nom { border-left-color: var(--or); }
    td.zero { color: #c5c5c5; }
    tbody td.sep, tfoot td.sep { border-left: 1px solid #eef1ef; }

    tfoot td {
        background: var(--or-pale); font-weight: 800; color: #1a1a1a;
        border-bottom: none; border-top: 3px solid var(--or);
    }

    .pastille { display: inline-block; min-width: 26px; padding: 3px 11px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .p-attente { background: #fbe9a8; color: #7a5d05; }
    .p-ok { background: #e8f5e9; color: var(--vert); }
    .p-refus, .p-hors { background: #fdeaea; color: #d9534f; }
    .vide { text-align: center; color: #999; padding: 30px; font-size: 13px; }

    .pied-table { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 12px; font-size: 13px; color: #666; }
    .pagination { display: flex; align-items: center; gap: 6px; }
    .btn-page { padding: 7px 14px; border: 1px solid #ddd; border-radius: 16px; background: white; color: var(--vert); font-size: 12.5px; font-weight: 700; font-family: inherit; cursor: pointer; transition: .2s ease; }
    .btn-page:hover:not(:disabled) { background: #e8f5e9; border-color: var(--vert); }
    .btn-page:disabled { color: #aaa; cursor: default; }
    .num-page { font-weight: 700; color: #333; padding: 0 4px; }

    @media (max-width: 900px) { .resume { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) {
        .resume { grid-template-columns: 1fr; }
        .recherche { min-width: 0; width: 100%; }
        .barre-controles { align-items: stretch; }
    }
</style>

<div class="page-header">
    <div>
        <h2>Récapitulatif par direction</h2>
        <div class="sous-titre">Salles, utilisateurs et réservations de chaque direction.</div>
    </div>
</div>

@if ($sallesSansDirection > 0)
    <div class="alerte-direction">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
        <span>{{ $sallesSansDirection }} salle(s) ne sont rattachées à aucune direction et ne figurent pas dans ce tableau. <a href="{{ route('admin.salles.index') }}">Attribuer une direction</a></span>
    </div>
@endif

<div class="resume">
    <div class="resume-carte"><div class="valeur">{{ $lignes->count() }}</div><div class="libelle">Directions</div></div>
    <div class="resume-carte"><div class="valeur">{{ $totaux['salles'] }}</div><div class="libelle">Salles</div></div>
    <div class="resume-carte"><div class="valeur">{{ $totaux['agents'] + $totaux['gestionnaires'] + $totaux['admins'] }}</div><div class="libelle">Utilisateurs</div></div>
    <div class="resume-carte"><div class="valeur">{{ $totaux['res_total'] }}</div><div class="libelle">Réservations</div></div>
</div>

<div class="barre-controles">
    <div class="afficher">
        Afficher
        <select id="tailleListe">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        directions
    </div>
    <label class="recherche">
        <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" id="rechercheDirection" placeholder="Rechercher une direction...">
    </label>
</div>

<div class="table-container">
    <table id="tableRecap">
        <thead>
            <tr class="groupe">
                <th rowspan="2" style="vertical-align: bottom;">Direction</th>
                <th rowspan="2" style="vertical-align: bottom;">Services</th>
                <th colspan="3" class="sep">Salles</th>
                <th colspan="3" class="sep">Utilisateurs</th>
                <th colspan="4" class="sep">Réservations</th>
            </tr>
            <tr class="colonnes">
                <th class="sep">Total</th>
                <th>Disponibles</th>
                <th>Hors service</th>
                <th class="sep">Agents</th>
                <th>Gestionnaires</th>
                <th>Admins</th>
                <th class="sep">Total</th>
                <th>En attente</th>
                <th>Confirmées</th>
                <th>Refusées</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lignes as $l)
                <tr>
                    <td class="nom">{{ $l['nom'] }}</td>
                    <td class="{{ $l['services'] ? '' : 'zero' }}">{{ $l['services'] }}</td>

                    <td class="sep {{ $l['salles'] ? '' : 'zero' }}">{{ $l['salles'] }}</td>
                    <td class="{{ $l['salles_dispo'] ? '' : 'zero' }}">{{ $l['salles_dispo'] }}</td>
                    <td class="{{ $l['salles_hors'] ? '' : 'zero' }}">
                        @if ($l['salles_hors'] > 0)<span class="pastille p-hors">{{ $l['salles_hors'] }}</span>@else 0 @endif
                    </td>

                    <td class="sep {{ $l['agents'] ? '' : 'zero' }}">{{ $l['agents'] }}</td>
                    <td class="{{ $l['gestionnaires'] ? '' : 'zero' }}">{{ $l['gestionnaires'] }}</td>
                    <td class="{{ $l['admins'] ? '' : 'zero' }}">{{ $l['admins'] }}</td>

                    <td class="sep {{ $l['res_total'] ? '' : 'zero' }}">{{ $l['res_total'] }}</td>
                    <td class="{{ $l['res_attente'] ? '' : 'zero' }}">
                        @if ($l['res_attente'] > 0)<span class="pastille p-attente">{{ $l['res_attente'] }}</span>@else 0 @endif
                    </td>
                    <td class="{{ $l['res_confirmee'] ? '' : 'zero' }}">
                        @if ($l['res_confirmee'] > 0)<span class="pastille p-ok">{{ $l['res_confirmee'] }}</span>@else 0 @endif
                    </td>
                    <td class="{{ $l['res_refusee'] ? '' : 'zero' }}">
                        @if ($l['res_refusee'] > 0)<span class="pastille p-refus">{{ $l['res_refusee'] }}</span>@else 0 @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" class="vide">Aucune direction enregistrée.</td></tr>
            @endforelse
            <tr id="ligneAucun" style="display:none;"><td colspan="12" class="vide">Aucune direction ne correspond à la recherche.</td></tr>
        </tbody>
        @if ($lignes->count() > 0)
        <tfoot>
            <tr>
                <td>Total</td>
                <td>{{ $totaux['services'] }}</td>
                <td class="sep">{{ $totaux['salles'] }}</td>
                <td>{{ $totaux['salles_dispo'] }}</td>
                <td>{{ $totaux['salles_hors'] }}</td>
                <td class="sep">{{ $totaux['agents'] }}</td>
                <td>{{ $totaux['gestionnaires'] }}</td>
                <td>{{ $totaux['admins'] }}</td>
                <td class="sep">{{ $totaux['res_total'] }}</td>
                <td>{{ $totaux['res_attente'] }}</td>
                <td>{{ $totaux['res_confirmee'] }}</td>
                <td>{{ $totaux['res_refusee'] }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<div class="pied-table">
    <span id="infoListe"></span>
    <div class="pagination" id="paginationListe">
        <button type="button" class="btn-page" id="btnPrec">Précédent</button>
        <span class="num-page" id="numPage"></span>
        <button type="button" class="btn-page" id="btnSuiv">Suivant</button>
    </div>
</div>

<script>
    var page = 1;
    var taille = 25;

    function afficher() {
        var q = document.getElementById('rechercheDirection').value.toLowerCase().trim();

        var lignes = Array.prototype.slice.call(document.querySelectorAll('#tableRecap tbody tr'))
            .filter(function (tr) { return tr.querySelector('td.nom'); });
        var ok = lignes.filter(function (tr) {
            return tr.querySelector('td.nom').textContent.toLowerCase().includes(q);
        });

        var total = ok.length;
        var pages = Math.max(1, Math.ceil(total / taille));
        if (page > pages) page = pages;
        var debut = (page - 1) * taille;

        lignes.forEach(function (tr) { tr.style.display = 'none'; });
        ok.slice(debut, debut + taille).forEach(function (tr) { tr.style.display = ''; });

        document.getElementById('ligneAucun').style.display = (lignes.length > 0 && total === 0) ? '' : 'none';

        document.getElementById('infoListe').textContent = total === 0
            ? ''
            : 'Affichage de ' + (debut + 1) + ' à ' + Math.min(debut + taille, total) + ' sur ' + total + ' directions';
        document.getElementById('numPage').textContent = page + ' / ' + pages;
        document.getElementById('btnPrec').disabled = page <= 1;
        document.getElementById('btnSuiv').disabled = page >= pages;
        document.getElementById('paginationListe').style.display = pages > 1 ? 'flex' : 'none';
    }

    document.getElementById('rechercheDirection').addEventListener('input', function () { page = 1; afficher(); });
    document.getElementById('tailleListe').addEventListener('change', function () {
        taille = parseInt(this.value, 10);
        page = 1;
        afficher();
    });
    document.getElementById('btnPrec').addEventListener('click', function () { page--; afficher(); });
    document.getElementById('btnSuiv').addEventListener('click', function () { page++; afficher(); });
    afficher();
</script>
@endsection