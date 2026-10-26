@extends('layouts.dgi')

@section('title', 'Ajouter une localité - DGI')

@section('content')
<style>
    .form-card { background: white; border-radius: 8px; padding: 25px 30px; max-width: 600px; margin: 0 auto; }
    .form-card h2 { color: #1A8969; margin-bottom: 20px; }
    label { display: block; margin-bottom: 6px; font-size: 13px; color: #555; }
    input { width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; margin-bottom: 12px; }
    .error { color: #d9534f; font-size: 12px; margin-top: -8px; margin-bottom: 10px; }
    .secteur-row { display: flex; gap: 8px; margin-bottom: 8px; }
    .secteur-row input { margin-bottom: 0; }
    .btn-retirer { background: #d9534f; color: white; border: none; border-radius: 4px; padding: 0 12px; cursor: pointer; }
    .btn-ajouter-secteur { background: #f0f0f0; border: 1px dashed #999; padding: 8px; width: 100%; border-radius: 4px; cursor: pointer; margin-bottom: 15px; }
    .buttons { display: flex; gap: 10px; margin-top: 10px; }
    .btn { padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 13px; }
    .btn-save { background: #1A8969; color: white; }
    .btn-cancel { background: #777; color: white; }
</style>

<div class="form-card">
    <h2>Ajouter une localité</h2>

    <form method="POST" action="{{ route('admin.localites.store') }}">
        @csrf

        <label>Nom de la ville</label>
        <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Exemple : Ouagadougou">
        @error('nom') <div class="error">{{ $message }}</div> @enderror

        <label>Secteurs de cette ville</label>
        <div id="listeSecteurs">
            <div class="secteur-row">
                <input type="text" name="secteurs[]" placeholder="Exemple : Secteur 1">
                <button type="button" class="btn-retirer" onclick="retirerLigne(this)">✕</button>
            </div>
        </div>

        <button type="button" class="btn-ajouter-secteur" onclick="ajouterLigne()">+ Ajouter un autre secteur</button>

        <div class="buttons">
            <button type="submit" class="btn btn-save">Enregistrer</button>
            <a href="{{ route('admin.localites.index') }}" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>

<script>
function ajouterLigne() {
    const div = document.createElement('div');
    div.className = 'secteur-row';
    div.innerHTML = '<input type="text" name="secteurs[]" placeholder="Nom du secteur"><button type="button" class="btn-retirer" onclick="retirerLigne(this)">✕</button>';
    document.getElementById('listeSecteurs').appendChild(div);
}
function retirerLigne(bouton) {
    const lignes = document.querySelectorAll('.secteur-row');
    if (lignes.length > 1) {
        bouton.parentElement.remove();
    }
}
</script>
@endsection