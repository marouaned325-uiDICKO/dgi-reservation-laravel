@extends('layouts.dgi')

@section('title', 'Motifs - DGI')

@section('content')
@php $estAdmin = auth()->user()->role === 'admin'; @endphp
<style>
    .page-header { margin-bottom: 20px; }
    .page-header h2 { color: #1A8969; font-size: 20px; }
    .page-header .sous-titre { color: #888; font-size: 13px; margin-top: 3px; }
    .svg { width: 16px; height: 16px; flex-shrink: 0; }

    .message-erreur { padding: 12px 16px; margin-bottom: 15px; border-radius: 8px; background: #fdeaea; color: #d9534f; font-size: 14px; }

    .carte-ajout { background: white; border: 1px solid #eef1ef; border-top: 4px solid #D4AF37; border-radius: 12px; padding: 20px 24px; margin-bottom: 22px; }
    .carte-ajout h3 { font-size: 15px; color: #1A8969; margin-bottom: 4px; }
    .carte-ajout .aide { font-size: 12.5px; color: #888; margin-bottom: 14px; }
    .ligne-ajout { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-start; }
    .ligne-ajout .champ { flex: 1 1 260px; }
    .ligne-ajout .champ-dir { flex: 0 1 240px; }
    .ligne-ajout input[type="text"], .ligne-ajout select, .champ-modale input { width: 100%; padding: 11px 13px; border: 1.3px solid #ddd; border-radius: 9px; font-size: 14px; background: #fafafa; font-family: inherit; }
    .ligne-ajout input:focus, .ligne-ajout select:focus, .champ-modale input:focus { outline: none; border-color: #1A8969; background: white; box-shadow: 0 0 0 3px rgba(26,137,105,.12); }
    .erreur { color: #d9534f; font-size: 12px; margin-top: 5px; }

    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 11px 22px; border-radius: 20px; border: none; cursor: pointer; font-size: 13px; font-weight: 700; font-family: inherit; transition: .2s ease; }
    .btn-add { background: #1A8969; color: white; box-shadow: 0 2px 8px rgba(26,137,105,.3); }
    .btn-add:hover { background: #146e54; }

    .barre-controles { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .barre-controles select, .recherche input { border: 1px solid #e0e0e0; border-radius: 10px; padding: 10px 12px; font-size: 13px; background: white; font-family: inherit; }
    .recherche input { min-width: 240px; outline: none; }
    .recherche input:focus, .barre-controles select:focus { border-color: #1A8969; }
    .zone-gauche { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .zone-droite { display: flex; gap: 10px; flex-wrap: wrap; }
    .afficher { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; }
    .afficher select { padding: 8px 10px; }

    .table-wrapper { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #eef1ef; }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #1A8969; }
    thead th { color: white; text-align: left; padding: 13px 18px; font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
    tbody tr { border-bottom: 1px solid #f0f0f0; transition: background .15s ease; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #fffaf0; }
    tbody td { padding: 13px 18px; font-size: 13.5px; color: #333; vertical-align: middle; }
    td.nom { font-weight: 700; color: #1A8969; }
    .tag { background: #e8f5e9; color: #1A8969; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
    td.actions { white-space: nowrap; text-align: right; width: 200px; }
    .btn-mini { display: inline-flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 16px; border: 1.3px solid; background: white; cursor: pointer; font-size: 12px; font-weight: 700; font-family: inherit; transition: .2s ease; }
    .btn-mini .svg { width: 13px; height: 13px; }
    .btn-modif { color: #b8941f; border-color: #D4AF37; }
    .btn-modif:hover { background: #D4AF37; color: white; }
    .btn-suppr { color: #d9534f; border-color: #d9534f; margin-left: 6px; }
    .btn-suppr:hover { background: #d9534f; color: white; }
    .vide { text-align: center; color: #999; padding: 30px; }

    .pied-table { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 12px; font-size: 13px; color: #666; }
    .pagination { display: flex; align-items: center; gap: 6px; }
    .btn-page { padding: 7px 14px; border: 1px solid #ddd; border-radius: 16px; background: white; color: #1A8969; font-size: 12.5px; font-weight: 700; font-family: inherit; cursor: pointer; transition: .2s ease; }
    .btn-page:hover:not(:disabled) { background: #e8f5e9; border-color: #1A8969; }
    .btn-page:disabled { color: #aaa; cursor: default; }
    .num-page { font-weight: 700; color: #333; padding: 0 4px; }

    .modale-fond { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
    .modale-fond.ouverte { display: flex; }
    .modale { background: white; border-radius: 14px; max-width: 400px; width: 100%; padding: 26px 28px; box-shadow: 0 10px 40px rgba(0,0,0,.25); border-top: 4px solid #D4AF37; }
    .modale h3 { margin: 0 0 8px; color: #1a1a1a; font-size: 17px; }
    .modale p { margin: 0 0 18px; color: #777; font-size: 13.5px; line-height: 1.5; }
    .champ-modale { margin-bottom: 18px; }
    .boutons-modale { display: flex; gap: 10px; }
    .boutons-modale button { flex: 1; padding: 10px 0; border-radius: 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: .2s ease; }
    .b-annuler { background: white; color: #777; border: 1.3px solid #ccc; }
    .b-annuler:hover { background: #f5f5f5; }
    .b-valider { background: #1A8969; color: white; border: 1.3px solid #1A8969; }
    .b-valider:hover { background: #146e54; }
    .b-danger { background: #d9534f; color: white; border: 1.3px solid #d9534f; }
    .b-danger:hover { background: #c0392b; }

    @media (max-width: 650px) { .table-wrapper { overflow-x: auto; } table { min-width: 520px; } }
</style>

<div class="page-header">
    <h2>Motifs de réservation</h2>
    <div class="sous-titre">
        {{ $motifs->count() }} motif(s) enregistré(s){{ $estAdmin ? '' : ' pour votre direction' }}. Ils sont proposés aux agents lorsqu'ils réservent une salle de la direction.
    </div>
</div>

@if (session('error')) <div class="message-erreur">{{ session('error') }}</div> @endif
@if ($errors->has('nom')) <div class="message-erreur">{{ $errors->first('nom') }}</div> @endif

<div class="carte-ajout">
    <h3>Ajouter des motifs</h3>
    <div class="aide">Vous pouvez en saisir plusieurs à la fois en les séparant par une virgule. Exemple : Réunion, Formation, Séminaire</div>

    <form method="POST" action="{{ route('admin.motifs.store') }}">
        @csrf
        <div class="ligne-ajout">
            @if ($estAdmin)
                <div class="champ-dir">
                    <select name="direction_id" required>
                        <option value="">-- Direction --</option>
                        @foreach ($directions as $dir)
                            <option value="{{ $dir->id }}" @selected(old('direction_id') == $dir->id)>{{ $dir->nom_direction }}</option>
                        @endforeach
                    </select>
                    @error('direction_id') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            @endif
            <div class="champ">
                <input type="text" name="noms" value="{{ old('noms') }}" placeholder="Nom du motif" maxlength="1000" required>
                @error('noms') <div class="erreur">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-add">
                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter
            </button>
        </div>
    </form>
</div>

<div class="barre-controles">
    <div class="zone-gauche">
        <div class="afficher">
            Afficher
            <select id="tailleListe">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            motifs
        </div>
        <div class="recherche"><input type="text" id="recherche" placeholder="Rechercher un motif..."></div>
    </div>
    @if ($estAdmin)
        <div class="zone-droite">
            <select id="filtreDirection">
                <option value="">Toutes les directions</option>
                @foreach ($directions as $dir)
                    <option value="{{ $dir->id }}">{{ $dir->nom_direction }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Motif</th>
                @if ($estAdmin) <th>Direction</th> @endif
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody id="liste">
            @forelse ($motifs as $e)
                <tr class="ligne" data-direction="{{ $e->direction_id }}" data-nom="{{ mb_strtolower($e->nom . ' ' . ($e->direction->nom_direction ?? '')) }}">
                    <td class="nom">{{ $e->nom }}</td>
                    @if ($estAdmin)
                        <td><span class="tag">{{ $e->direction->nom_direction ?? 'Direction supprimée' }}</span></td>
                    @endif
                    <td class="actions">
                        <button type="button" class="btn-mini btn-modif"
                                onclick="ouvrirModification('{{ route('admin.motifs.update', $e) }}', @js($e->nom))">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg> Modifier
                        </button>
                        <button type="button" class="btn-mini btn-suppr"
                                onclick="ouvrirSuppression('{{ route('admin.motifs.destroy', $e) }}', @js($e->nom))">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg> Supprimer
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $estAdmin ? 3 : 2 }}" class="vide">Aucun motif enregistré.</td></tr>
            @endforelse
            <tr id="ligneAucun" style="display:none;"><td colspan="{{ $estAdmin ? 3 : 2 }}" class="vide">Aucun motif ne correspond à la recherche.</td></tr>
        </tbody>
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

{{-- Modale modification --}}
<div class="modale-fond" id="modaleModif">
    <div class="modale">
        <h3>Modifier le motif</h3>
        <p>Les réservations déjà faites gardent leur motif d'origine.</p>
        <form method="POST" id="formModif" action="">
            @csrf
            @method('PUT')
            <div class="champ-modale">
                <input type="text" name="nom" id="champNom" maxlength="100" required>
            </div>
            <div class="boutons-modale">
                <button type="button" class="b-annuler" onclick="fermerModales()">Annuler</button>
                <button type="submit" class="b-valider">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- Modale suppression --}}
<div class="modale-fond" id="modaleSuppr">
    <div class="modale">
        <h3>Supprimer ce motif ?</h3>
        <p id="texteSuppr"></p>
        <form method="POST" id="formSuppr" action="">
            @csrf
            @method('DELETE')
            <div class="boutons-modale">
                <button type="button" class="b-annuler" onclick="fermerModales()">Annuler</button>
                <button type="submit" class="b-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
var page = 1;
var taille = 25;

function afficher() {
    var q = document.getElementById('recherche').value.toLowerCase();
    var filtre = document.getElementById('filtreDirection');
    var dir = filtre ? filtre.value : '';

    var lignes = Array.prototype.slice.call(document.querySelectorAll('#liste .ligne'));
    var ok = lignes.filter(function (l) {
        return l.dataset.nom.includes(q) && (dir === '' || l.dataset.direction === dir);
    });

    var total = ok.length;
    var pages = Math.max(1, Math.ceil(total / taille));
    if (page > pages) page = pages;
    var debut = (page - 1) * taille;

    lignes.forEach(function (l) { l.style.display = 'none'; });
    ok.slice(debut, debut + taille).forEach(function (l) { l.style.display = ''; });

    document.getElementById('ligneAucun').style.display = (lignes.length > 0 && total === 0) ? '' : 'none';

    document.getElementById('infoListe').textContent = total === 0
        ? ''
        : 'Affichage de ' + (debut + 1) + ' à ' + Math.min(debut + taille, total) + ' sur ' + total + ' motifs';
    document.getElementById('numPage').textContent = page + ' / ' + pages;
    document.getElementById('btnPrec').disabled = page <= 1;
    document.getElementById('btnSuiv').disabled = page >= pages;
    document.getElementById('paginationListe').style.display = 'flex';}

function filtrer() { page = 1; afficher(); }

document.getElementById('recherche').addEventListener('input', filtrer);
var filtreDir = document.getElementById('filtreDirection');
if (filtreDir) filtreDir.addEventListener('change', filtrer);
document.getElementById('tailleListe').addEventListener('change', function () {
    taille = parseInt(this.value, 10);
    page = 1;
    afficher();
});
document.getElementById('btnPrec').addEventListener('click', function () { page--; afficher(); });
document.getElementById('btnSuiv').addEventListener('click', function () { page++; afficher(); });
afficher();

function fermerModales() {
    document.querySelectorAll('.modale-fond').forEach(function (m) { m.classList.remove('ouverte'); });
}
function ouvrirModification(url, nom) {
    document.getElementById('formModif').action = url;
    document.getElementById('champNom').value = nom;
    document.getElementById('modaleModif').classList.add('ouverte');
    document.getElementById('champNom').focus();
}
function ouvrirSuppression(url, nom) {
    document.getElementById('formSuppr').action = url;
    document.getElementById('texteSuppr').textContent = 'Voulez-vous vraiment supprimer "' + nom + '" ? Cette action est irréversible.';
    document.getElementById('modaleSuppr').classList.add('ouverte');
}
document.querySelectorAll('.modale-fond').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) fermerModales(); });
});
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fermerModales(); });
</script>
@endsection