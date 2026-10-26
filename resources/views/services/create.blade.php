@extends('layouts.dgi')

@section('title', 'Ajouter un service - DGI')

@section('content')
<style>
    .form-card {
        background: white; border-radius: 12px; padding: 30px 35px;
        max-width: 650px; margin: 0 auto;
        border: 1px solid #eef1ef;
        border-top: 4px solid #1A8969;
    }
    .form-card h2 { color: #1A8969; font-size: 20px; margin-bottom: 4px; }
    .form-subtitle { color: #888; font-size: 13px; margin-bottom: 22px; }

    .svg { width: 16px; height: 16px; flex-shrink: 0; }
    .entete-page { max-width: 650px; margin: 0 auto 14px; display: flex; justify-content: flex-end; }
    .btn-retour {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 18px; border-radius: 30px; border: 1px solid #1A8969;
        background: white; color: #1A8969; font-size: 13px; font-weight: 600;
        text-decoration: none; transition: 0.2s;
    }
    .btn-retour:hover { background: #1A8969; color: white; }

    label.champ-label { display: block; margin: 14px 0 6px; font-size: 13px; font-weight: 600; color: #444; }
    label.champ-label:first-of-type { margin-top: 0; }

    .form-card input[type="text"], .form-card select {
        width: 100%; padding: 10px 12px; border: 1.3px solid #ddd; border-radius: 8px;
        font-size: 14px; color: #333; transition: 0.2s ease; background: #fafafa; font-family: inherit;
    }
    .form-card input[type="text"]:focus, .form-card select:focus {
        outline: none; border-color: #1A8969; background: white;
        box-shadow: 0 0 0 3px rgba(26, 137, 105, 0.12);
    }
    .form-card .invalide { border-color: #d9534f; background: #fff8f8; }
    .error { color: #d9534f; font-size: 12px; margin-top: 6px; }

    .alerte-vide {
        background: #fff3cd; color: #856404; padding: 14px 16px; border-radius: 10px;
        font-size: 13.5px; line-height: 1.5; margin-bottom: 8px;
    }
    .alerte-vide a { color: #856404; font-weight: 700; }

    .buttons { display: flex; gap: 10px; margin-top: 24px; border-top: 1px solid #f0f0f0; padding-top: 20px; }
    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        padding: 10px 24px; border-radius: 20px; text-decoration: none;
        border: none; cursor: pointer; font-size: 13px; font-weight: 700; font-family: inherit; transition: 0.2s ease;
    }
    .btn-save { background: #1A8969; color: white; box-shadow: 0 2px 8px rgba(26, 137, 105, 0.3); }
    .btn-save:hover { background: #146e54; transform: translateY(-1px); }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .btn-cancel { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-cancel:hover { background: #f5f5f5; border-color: #999; }

    .grille-services { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 12px; }
    .service-row { display: flex; align-items: center; gap: 6px; }
    .service-row .numero {
        width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%;
        background: #e3f4ee; color: #1A8969; font-size: 11px; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .service-row input[type="text"] { flex: 1; min-width: 0; }
    .btn-retirer {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 38px; flex-shrink: 0; background: white; color: #d9534f;
        border: 1.3px solid #f0c4c2; border-radius: 8px; cursor: pointer; transition: 0.2s ease;
    }
    .btn-retirer:hover { background: #d9534f; color: white; border-color: #d9534f; }
    .btn-retirer:disabled { opacity: 0.35; cursor: not-allowed; background: white; color: #d9534f; }
    .btn-ajouter-service {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        background: #fafafa; border: 1.3px dashed #bbb; color: #666;
        padding: 11px; width: 100%; border-radius: 8px; cursor: pointer;
        font-size: 13px; font-weight: 600; font-family: inherit; transition: 0.2s ease;
    }
    .btn-ajouter-service:hover { border-color: #1A8969; color: #1A8969; background: #f3f9f5; }

    .existants { margin-top: 10px; padding: 10px 12px; background: #f6faf8; border: 1px dashed #cfe5dc; border-radius: 8px; }
    .existants-titre { font-size: 12px; color: #777; }
    .existants .aucun { font-size: 12px; color: #999; }
    .liste-services-direction { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .service-tag {
        background: #e8f5e9; color: #1A8969; padding: 4px 12px;
        border-radius: 20px; font-size: 11.5px; font-weight: 600;
    }

    @media (max-width: 560px) {
        .form-card { padding: 22px 18px; }
        .grille-services { grid-template-columns: 1fr; }
        .buttons { flex-direction: column-reverse; }
        .btn { width: 100%; }
    }
</style>

<div class="entete-page">
    <a href="{{ route('admin.services.index') }}" class="btn-retour"><svg class="svg" style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg> Retour</a>
</div>

<div class="form-card">
    <h2>Ajouter un service</h2>
    <p class="form-subtitle">Un service appartient toujours à une direction déjà créée.</p>

    @if ($directions->isEmpty())
        <div class="alerte-vide">
            Aucune direction n'existe encore : il faut d'abord créer une direction avant de pouvoir ajouter un service.
            <br><a href="{{ route('admin.directions.create') }}">Créer une direction <svg class="svg" style="width:14px;height:14px;vertical-align:-2px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="buttons">
            <a href="{{ route('admin.services.index') }}" class="btn btn-cancel">Retour aux services</a>
        </div>
    @else
    <form method="POST" action="{{ route('admin.services.store') }}" id="formService">
        @csrf

        <label class="champ-label" for="direction_id">Direction</label>
        <select id="direction_id" name="direction_id" class="@error('direction_id') invalide @enderror" required>
            <option value="">Sélectionner une direction</option>
            @foreach ($directions as $direction)
                <option value="{{ $direction->id }}" @selected((string) old('direction_id', $directionChoisie ?? request('direction')) === (string) $direction->id)>
                    {{ $direction->nom_direction }}
                </option>
            @endforeach
        </select>
        @error('direction_id') <div class="error">{{ $message }}</div> @enderror
        <div id="servicesExistants" class="existants" style="display:none;">
            <span class="existants-titre">Services déjà dans cette direction :</span>
            <div class="liste-services-direction" id="tagsExistants"></div>
        </div>

        <label class="champ-label">Services <span style="color:#999;font-weight:400;">(un ou plusieurs)</span></label>
        <div id="listeServices" class="grille-services">
            @foreach (old('services') ?: [''] as $ancien)
                <div class="service-row">
                    <span class="numero"></span>
                    <input type="text" name="services[]" value="{{ $ancien }}" placeholder="Nom du service" maxlength="100">
                    <button type="button" class="btn-retirer" onclick="retirerLigne(this)" title="Retirer"><svg class="svg" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
                </div>
            @endforeach
        </div>
        @error('services') <div class="error" style="margin-bottom:8px;">{{ $message }}</div> @enderror
        @error('services.*') <div class="error" style="margin-bottom:8px;">{{ $message }}</div> @enderror

        <button type="button" class="btn-ajouter-service" onclick="ajouterLigne()"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Ajouter un autre service</button>

        <div class="buttons">
            <button type="submit" class="btn btn-save" id="btnEnregistrer"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg> Enregistrer</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-cancel">Annuler</a>
        </div>
    </form>

    <script>
    const iconeCroix = '<svg class="svg" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>';
    const liste = document.getElementById('listeServices');

    function mettreAJour() {
        const lignes = liste.querySelectorAll('.service-row');
        lignes.forEach(function (ligne, i) {
            ligne.querySelector('.numero').textContent = i + 1;
            ligne.querySelector('.btn-retirer').disabled = lignes.length === 1;
        });
    }
    function ajouterLigne() {
        const div = document.createElement('div');
        div.className = 'service-row';
        div.innerHTML = '<span class="numero"></span>' +
            '<input type="text" name="services[]" placeholder="Nom du service" maxlength="100">' +
            '<button type="button" class="btn-retirer" onclick="retirerLigne(this)" title="Retirer">' + iconeCroix + '</button>';
        liste.appendChild(div);
        mettreAJour();
        div.querySelector('input').focus();
    }
    function retirerLigne(bouton) {
        if (liste.querySelectorAll('.service-row').length > 1) {
            bouton.closest('.service-row').remove();
            mettreAJour();
        }
    }
    // Entrée : passe au service suivant ou en ajoute un, sans envoyer le formulaire
    liste.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.matches('input[name="services[]"]')) {
            e.preventDefault();
            const lignes = liste.querySelectorAll('.service-row');
            const derniere = lignes[lignes.length - 1].querySelector('input');
            if (e.target === derniere && e.target.value.trim() !== '') {
                ajouterLigne();
            } else {
                const suivant = e.target.closest('.service-row').nextElementSibling;
                if (suivant) suivant.querySelector('input').focus();
            }
        }
    });
    document.getElementById('formService').addEventListener('submit', function () {
        const b = document.getElementById('btnEnregistrer');
        b.disabled = true;
        b.textContent = 'Enregistrement...';
    });

    const servicesParDirection = @json($directions->load('services')->mapWithKeys(fn ($d) => [$d->id => $d->services->pluck('nom_service')]));
    const selectDirection = document.getElementById('direction_id');
    function afficherExistants() {
        const bloc = document.getElementById('servicesExistants');
        const tags = document.getElementById('tagsExistants');
        const noms = servicesParDirection[selectDirection.value];
        tags.innerHTML = '';
        if (!selectDirection.value) { bloc.style.display = 'none'; return; }
        bloc.style.display = '';
        if (!noms || noms.length === 0) {
            tags.innerHTML = '<span class="aucun">Aucun service pour le moment.</span>';
            return;
        }
        noms.forEach(function (n) {
            const s = document.createElement('span');
            s.className = 'service-tag';
            s.textContent = n;
            tags.appendChild(s);
        });
    }
    selectDirection.addEventListener('change', afficherExistants);
    afficherExistants();
    mettreAJour();
    </script>
    @endif
</div>
@endsection