@extends('layouts.dgi')

@section('title', 'Modifier une direction - DGI')

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

    .buttons { display: flex; gap: 10px; margin-top: 24px; border-top: 1px solid #f0f0f0; padding-top: 20px; }
    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        padding: 10px 24px; border-radius: 20px; text-decoration: none;
        border: none; cursor: pointer; font-size: 13px; font-weight: 700; transition: 0.2s ease;
    }
    .btn-save { background: #1A8969; color: white; box-shadow: 0 2px 8px rgba(26, 137, 105, 0.3); }
    .btn-save:hover { background: #146e54; transform: translateY(-1px); }
    .btn-cancel { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-cancel:hover { background: #f5f5f5; border-color: #999; }

    .liste-services-direction { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .service-tag {
        background: #e8f5e9; color: #1A8969; padding: 4px 12px;
        border-radius: 20px; font-size: 11.5px; font-weight: 600;
    }

    @media (max-width: 560px) {
        .form-card { padding: 22px 18px; }
        .buttons { flex-direction: column-reverse; }
        .btn { width: 100%; }
    }
</style>

<div class="entete-page">
    <a href="{{ route('admin.directions.index') }}" class="btn-retour"><svg class="svg" style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg> Retour</a>
</div>

<div class="form-card">
    <h2>Modifier la direction</h2>
    <p class="form-subtitle">Modifiez le nom de la direction.</p>

    <form method="POST" action="{{ route('admin.directions.update', $direction) }}">
        @csrf
        @method('PUT')

        <label class="champ-label" for="nom_direction">Nom de la direction</label>
        <input type="text" id="nom_direction" name="nom_direction"
               value="{{ old('nom_direction', $direction->nom_direction) }}"
               required class="@error('nom_direction') invalide @enderror">
        @error('nom_direction') <div class="error">{{ $message }}</div> @enderror

        <label class="champ-label">Services rattachés ({{ $direction->services->count() }})</label>
        <div class="liste-services-direction">
            @forelse ($direction->services as $service)
                <span class="service-tag">{{ $service->nom_service }}</span>
            @empty
                <span style="color:#999; font-size:12px;">Aucun service pour le moment.</span>
            @endforelse
        </div>
        <p style="margin-top:10px; font-size:12.5px;">
            <a href="{{ route('admin.services.index', ['direction' => $direction->id]) }}" style="color:#1A8969; font-weight:600;">Gérer les services de cette direction <svg class="svg" style="width:14px;height:14px;vertical-align:-2px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </p>

        <div class="buttons">
            <button type="submit" class="btn btn-save"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg> Enregistrer</button>
            <a href="{{ route('admin.directions.index') }}" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>
@endsection