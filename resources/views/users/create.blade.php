@extends('layouts.dgi')

@section('title', 'Ajouter un utilisateur - DGI')

@section('content')
<style>
    .form-card { background: white; border-radius: 14px; padding: 28px 32px; max-width: 620px; margin: 0 auto; border: 1px solid #eef1ef; border-top: 4px solid #1A8969; }
    .form-card h2 { color: #1A8969; font-size: 20px; margin-bottom: 20px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 16px; }
    label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #444; }
    label small { color: #999; font-weight: 400; }
    input, select { width: 100%; padding: 11px 13px; border: 1.3px solid #ddd; border-radius: 9px; font-size: 14px; background: #fafafa; font-family: inherit; }
    input:focus, select:focus { outline: none; border-color: #1A8969; background: white; box-shadow: 0 0 0 3px rgba(26,137,105,.12); }
    .error { color: #d9534f; font-size: 12px; margin-top: 5px; }
    .note-admin { display: none; background: #fff8e1; border: 1px solid #ecd58a; border-left: 5px solid #D4AF37; color: #7a5d05; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .note-info { background: #e8f5ef; border: 1px solid #bfe0d2; border-left: 5px solid #1A8969; color: #146e54; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .buttons { display: flex; gap: 10px; margin-top: 8px; }
    .btn { padding: 11px 26px; border-radius: 20px; text-decoration: none; border: none; cursor: pointer; font-size: 13px; font-weight: 700; font-family: inherit; }
    .btn-save { background: #1A8969; color: white; }
    .btn-save:hover { background: #146e54; }
    .btn-cancel { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-cancel:hover { background: #f5f5f5; }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } .form-card { padding: 20px; } }
</style>

<div class="form-card">
    <h2>Ajouter un utilisateur</h2>

    <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off">
        @csrf

        <div class="form-group">
            <label for="name">Nom et prénom</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Exemple : DICKO Marwane" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" value="{{ old('telephone') }}" maxlength="8" inputmode="numeric" placeholder="70000000" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 8);">
                @error('telephone') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row" id="ligneDirectionService">
            <div class="form-group">
                <label for="direction_id">Direction</label>
                <select name="direction_id" id="direction_id" onchange="mettreAJourServices()" required>
                    @if ($directions->count() > 1)
                        <option value="">-- Choisir --</option>
                    @endif
                    @foreach ($directions as $direction)
                        <option value="{{ $direction->id }}" @selected(old('direction_id', $directionParDefaut) == $direction->id)>{{ $direction->nom_direction }}</option>
                    @endforeach
                </select>
                @error('direction_id') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="service_id">Service</label>
                <select name="service_id" id="service_id"></select>
                @error('service_id') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="role">Rôle</label>
            <select name="role" id="role" onchange="basculerChampsAdmin()" required>
                <option value="">-- Choisir --</option>
                @foreach ($roles as $code => $libelle)
                    <option value="{{ $code }}" @selected(old('role') == $code)>{{ $libelle }}</option>
                @endforeach
            </select>
            @error('role') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div id="noteAdmin" class="note-admin">
            Un administrateur a accès à toutes les directions : aucune direction ni service n'est à renseigner.
        </div>

        <div class="note-info">
            Aucun mot de passe à saisir : l'utilisateur recevra par courriel un lien (valable 3 jours) pour se connecter et choisir le sien.
        </div>

        <div class="buttons">
            <button type="submit" class="btn btn-save">Enregistrer</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>

<script>
const servicesParDirection = @json($directions->mapWithKeys(fn ($d) => [$d->id => $d->services->map(fn ($s) => ['id' => $s->id, 'nom' => $s->nom_service])->values()]));
const serviceActuel = @js((string) old('service_id'));

function mettreAJourServices() {
    const dir = document.getElementById('direction_id').value;
    const select = document.getElementById('service_id');
    const liste = servicesParDirection[dir] || [];
    select.innerHTML = '<option value="">-- ' + (dir ? (liste.length ? 'Choisir un service' : 'Aucun service') : 'Choisir d\'abord une direction') + ' --</option>';
    liste.forEach(function (s) {
        const o = document.createElement('option');
        o.value = s.id; o.textContent = s.nom;
        if (String(s.id) === serviceActuel) o.selected = true;
        select.appendChild(o);
    });
}

function basculerChampsAdmin() {
    const estAdmin = document.getElementById('role').value === 'admin';
    document.getElementById('ligneDirectionService').style.display = estAdmin ? 'none' : '';
    document.getElementById('direction_id').disabled = estAdmin;
    document.getElementById('service_id').disabled = estAdmin;
    document.getElementById('noteAdmin').style.display = estAdmin ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    mettreAJourServices();
    basculerChampsAdmin();
});
</script>
@endsection