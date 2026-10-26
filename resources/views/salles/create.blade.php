@extends('layouts.dgi')

@section('title', 'Ajouter une salle - DGI')

@section('content')
@php
    $equipementsCoches = old('equipements', []);
@endphp

<style>
    .form-card { background: white; border-radius: 14px; padding: 28px 32px; max-width: 680px; margin: 0 auto; border: 1px solid #eef1ef; border-top: 4px solid #1A8969; }
    .form-card h2 { color: #1A8969; font-size: 20px; margin-bottom: 20px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-row.trois { grid-template-columns: 1.3fr 1fr 1fr; }
    .form-group { margin-bottom: 16px; }
    label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #444; }
    label small { color: #999; font-weight: 400; }
    label .requis { color: #d9534f; }
    input[type="text"], input[type="number"], input[type="file"], select { width: 100%; padding: 11px 13px; border: 1.3px solid #ddd; border-radius: 9px; font-size: 14px; background: #fafafa; font-family: inherit; }
    input:focus, select:focus { outline: none; border-color: #1A8969; background: white; box-shadow: 0 0 0 3px rgba(26,137,105,.12); }
    .error { color: #d9534f; font-size: 12px; margin-top: 5px; }
    .buttons { display: flex; gap: 10px; margin-top: 8px; }
    .btn { padding: 11px 26px; border-radius: 20px; text-decoration: none; border: none; cursor: pointer; font-size: 13px; font-weight: 700; font-family: inherit; }
    .btn-save { background: #1A8969; color: white; }
    .btn-save:hover { background: #146e54; }
    .btn-cancel { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-cancel:hover { background: #f5f5f5; }
    /* Aperçu de la photo */
    .apercu-photo { display: none; margin-top: 10px; width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; border: 1px solid #eef1ef; }
    .apercu-photo.visible { display: block; }
    .aide-photo { font-size: 12px; color: #999; margin-top: 5px; }
    /* Liste déroulante d'équipements (sélection multiple) */
    .ms { position: relative; }
    .ms-controle { display: flex; align-items: center; gap: 8px; min-height: 46px; padding: 6px 12px; border: 1.3px solid #ddd; border-radius: 9px; background: #fafafa; cursor: pointer; }
    .ms.ouvert .ms-controle, .ms-controle:focus { outline: none; border-color: #1A8969; background: white; box-shadow: 0 0 0 3px rgba(26,137,105,.12); }
    .ms-puces { display: flex; flex-wrap: wrap; gap: 6px; flex: 1; }
    .ms-placeholder { color: #999; font-size: 14px; flex: 1; }
    .ms-placeholder.cache { display: none; }
    .ms-fleche { width: 18px; height: 18px; color: #777; flex-shrink: 0; transition: transform .2s ease; }
    .ms.ouvert .ms-fleche { transform: rotate(180deg); }
    .ms-puce { display: inline-flex; align-items: center; gap: 6px; background: #e8f5e9; color: #1A8969; border: 1px solid #cfe8d6; padding: 4px 6px 4px 11px; border-radius: 16px; font-size: 12.5px; font-weight: 600; }
    .ms-puce button { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border: none; border-radius: 50%; background: #D4AF37; color: white; cursor: pointer; padding: 0; transition: .15s ease; }
    .ms-puce button:hover { background: #d9534f; }
    .ms-puce button svg { width: 10px; height: 10px; }
    .ms-liste { display: none; position: absolute; left: 0; right: 0; top: calc(100% + 6px); z-index: 50; background: white; border: 1px solid #eef1ef; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.16); overflow: hidden; }
    .ms.ouvert .ms-liste { display: block; }
    .ms-outils { display: flex; gap: 8px; padding: 10px; border-bottom: 1px solid #f0f0f0; background: #fffaf0; }
    .ms-outils input { flex: 1; padding: 8px 11px !important; font-size: 13px !important; }
    .ms-tout { border: 1.3px solid #1A8969; background: white; color: #1A8969; border-radius: 16px; padding: 7px 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; white-space: nowrap; transition: .2s ease; }
    .ms-tout:hover { background: #1A8969; color: white; }
    .ms-options { max-height: 220px; overflow-y: auto; }
    .ms-option { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 14px; font-size: 13.5px; color: #333; cursor: pointer; }
    .ms-option:hover { background: #f5faf7; }
    .ms-option.choisi { color: #1A8969; font-weight: 700; background: #f0f9f4; }
    .ms-option svg { width: 16px; height: 16px; color: #D4AF37; display: none; }
    .ms-option.choisi svg { display: block; }
    .ms-vide { padding: 14px; font-size: 13px; color: #999; text-align: center; }
    .note-equip { font-size: 12.5px; color: #888; margin-top: 8px; }
    .note-equip a { color: #1A8969; font-weight: 700; text-decoration: none; }
    .form-row.une { grid-template-columns: 1fr; }
    .note-equip a:hover { text-decoration: underline; }
    @media (max-width: 700px) { .form-row, .form-row.trois { grid-template-columns: 1fr; } .form-card { padding: 20px; } }
</style>

<div class="form-card">
    <h2>Ajouter une salle</h2>

    <form method="POST" action="{{ route('admin.salles.store') }}" enctype="multipart/form-data">
        @csrf

        @if (auth()->user()->role === 'admin')
            <div class="form-group">
                <label for="direction_id">Direction</label>
                <select id="direction_id" name="direction_id" required>
                    <option value="">-- Choisir une direction --</option>
                    @foreach ($directions as $direction)
                        <option value="{{ $direction->id }}" @selected(old('direction_id') == $direction->id)>{{ $direction->nom_direction }}</option>
                    @endforeach
                </select>
                @error('direction_id') <div class="error">{{ $message }}</div> @enderror
            </div>
        @endif

        <div class="form-group">
            <label for="nom">Nom de la salle</label>
            <input type="text" id="nom" name="nom" value="{{ old('nom') }}" placeholder="Exemple : Salle de conférence" required>
            @error('nom') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-row trois">
            <div class="form-group">
                <label for="localite">Localité (ville)</label>
                <select name="localite" id="localite">
                    <option value="">-- Sélectionner une ville --</option>
                    @foreach ($localites as $loc)
                        <option value="{{ $loc->nom }}" @selected(old('localite') == $loc->nom)>{{ $loc->nom }}</option>
                    @endforeach
                </select>
                @error('localite') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="capacite">Capacité de la salle</label>
                <input type="number" id="capacite" name="capacite" min="1" value="{{ old('capacite') }}" placeholder="Nombre de places">
                @error('capacite') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="statut">Statut de la salle</label>
                <select id="statut" name="statut">
                    @foreach (['disponible' => 'Disponible', 'indisponible' => 'Indisponible', 'maintenance' => 'Maintenance'] as $code => $libelle)
                        <option value="{{ $code }}" @selected(old('statut') == $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('statut') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Équipements de la salle</label>
            <div class="ms" id="ms">
                <div class="ms-controle" id="msControle" tabindex="0">
                    <div class="ms-puces" id="msPuces"></div>
                    <span class="ms-placeholder" id="msPlaceholder">Sélectionner des équipements</span>
                    <svg class="ms-fleche" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </div>
                <div class="ms-liste" id="msListe">
                    <div class="ms-outils">
                        <input type="text" id="msRecherche" placeholder="Rechercher un équipement..." autocomplete="off">
                        <button type="button" id="msTout" class="ms-tout">Tout sélectionner</button>
                    </div>
                    <div class="ms-options" id="msOptions"></div>
                </div>
            </div>
            <div id="msInputs"></div>
            <div class="note-equip" id="noteEquipements"></div>
            @error('equipements') <div class="error">{{ $message }}</div> @enderror
            @error('equipements.*') <div class="error">{{ $message }}</div> @enderror
            @error('equipement') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="photo">Photo de la salle <span class="requis">*</span></label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
            <div class="aide-photo">Image obligatoire (JPG, PNG...), 2 Mo maximum.</div>
            <img id="apercuPhoto" class="apercu-photo" alt="Aperçu de la photo">
            @error('photo') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="buttons">
            <button type="submit" class="btn btn-save">Enregistrer</button>
            <a href="{{ route('admin.salles.index') }}" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>

<script>
/* ----- Photo : aperçu et contrôle de la taille ----- */
document.getElementById('photo').addEventListener('change', function () {
    const apercu = document.getElementById('apercuPhoto');
    const fichier = this.files[0];

    if (!fichier) {
        apercu.classList.remove('visible');
        return;
    }
    if (fichier.size > 2 * 1024 * 1024) {
        alert('La photo dépasse 2 Mo. Choisissez une image plus légère.');
        this.value = '';
        apercu.classList.remove('visible');
        return;
    }
    apercu.src = URL.createObjectURL(fichier);
    apercu.classList.add('visible');
});

/* ----- Équipements : liste déroulante à sélection multiple ----- */
const catalogueEquipements = @json($catalogue);
const equipementsCoches = @json($equipementsCoches);
// null = administrateur (liste fusionnée de toutes les directions)
const directionFixe = @js(auth()->user()->role === 'admin' ? null : auth()->user()->direction_id);
const lienEquipements = @js(route('admin.equipements.index'));

let catalogueCourant = [];
let choisis = [];

const msEl = document.getElementById('ms');
const iconeCroix = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';
const iconeCoche = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';

/** Équipements proposés : tous (fusionnés) pour l'administrateur, ceux de sa direction sinon. */
function catalogueDisponible() {
    if (directionFixe === null) {
        return catalogueEquipements['*'] || [];
    }
    return catalogueEquipements[String(directionFixe)] || [];
}

function dessinerEquipements() {
    // Puces des équipements sélectionnés (avec croix pour retirer)
    const puces = document.getElementById('msPuces');
    puces.innerHTML = '';
    choisis.forEach(function (nom) {
        const puce = document.createElement('span');
        puce.className = 'ms-puce';
        puce.appendChild(document.createTextNode(nom));
        const croix = document.createElement('button');
        croix.type = 'button';
        croix.title = 'Retirer';
        croix.innerHTML = iconeCroix;
        croix.addEventListener('click', function (e) {
            e.stopPropagation();
            choisis = choisis.filter(n => n !== nom);
            dessinerEquipements();
        });
        puce.appendChild(croix);
        puces.appendChild(puce);
    });
    document.getElementById('msPlaceholder').classList.toggle('cache', choisis.length > 0);

    // Champs envoyés au serveur
    const champs = document.getElementById('msInputs');
    champs.innerHTML = '';
    choisis.forEach(function (nom) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'equipements[]';
        input.value = nom;
        champs.appendChild(input);
    });

    // Liste des options
    const recherche = document.getElementById('msRecherche').value.trim().toLowerCase();
    const options = document.getElementById('msOptions');
    options.innerHTML = '';
    const visibles = catalogueCourant.filter(n => n.toLowerCase().includes(recherche));

    if (visibles.length === 0) {
        const vide = document.createElement('div');
        vide.className = 'ms-vide';
        vide.textContent = catalogueCourant.length === 0 ? 'Aucun équipement disponible.' : 'Aucun résultat.';
        options.appendChild(vide);
    }
    visibles.forEach(function (nom) {
        const ligne = document.createElement('div');
        const estChoisi = choisis.includes(nom);
        ligne.className = 'ms-option' + (estChoisi ? ' choisi' : '');
        ligne.innerHTML = '<span></span>' + iconeCoche;
        ligne.firstChild.textContent = nom;
        ligne.addEventListener('click', function () {
            choisis = estChoisi ? choisis.filter(n => n !== nom) : choisis.concat(nom);
            dessinerEquipements();
        });
        options.appendChild(ligne);
    });

    // Bouton « Tout sélectionner » / « Tout désélectionner »
    const toutChoisi = catalogueCourant.length > 0 && catalogueCourant.every(n => choisis.includes(n));
    const bouton = document.getElementById('msTout');
    bouton.textContent = toutChoisi ? 'Tout désélectionner' : 'Tout sélectionner';
    bouton.disabled = catalogueCourant.length === 0;

    // Note sous le champ
    const note = document.getElementById('noteEquipements');
    if (catalogueDisponible().length === 0) {
        note.innerHTML = 'Aucun équipement enregistré. <a href="' + lienEquipements + '">Ajouter des équipements</a>';
    } else {
        note.innerHTML = 'Un équipement manque ? <a href="' + lienEquipements + '">Gérer les équipements</a>';
    }
}

function chargerCatalogue() {
    const catalogue = catalogueDisponible();

    // Les équipements déjà enregistrés sur la salle reprennent l'orthographe du catalogue
    // (même nom sans tenir compte des majuscules), sinon ils sont conservés tels quels.
    choisis = equipementsCoches.map(function (nom) {
        const trouve = catalogue.find(c => c.toLowerCase() === nom.toLowerCase());
        return trouve || nom;
    });
    choisis = Array.from(new Set(choisis));
    catalogueCourant = Array.from(new Set(catalogue.concat(choisis)));

    dessinerEquipements();
}

document.getElementById('msControle').addEventListener('click', function () { msEl.classList.toggle('ouvert'); });
document.getElementById('msControle').addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); msEl.classList.toggle('ouvert'); }
});
document.getElementById('msRecherche').addEventListener('input', dessinerEquipements);
document.getElementById('msRecherche').addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
document.getElementById('msTout').addEventListener('click', function () {
    const toutChoisi = catalogueCourant.every(n => choisis.includes(n));
    choisis = toutChoisi ? [] : catalogueCourant.slice();
    dessinerEquipements();
});
document.addEventListener('click', function (e) { if (!msEl.contains(e.target)) msEl.classList.remove('ouvert'); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') msEl.classList.remove('ouvert'); });

document.addEventListener('DOMContentLoaded', chargerCatalogue);
</script>
@endsection