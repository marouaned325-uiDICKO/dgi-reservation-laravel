@extends('layouts.dgi')

@section('title', 'Ajouter une direction - DGI')

@section('content')
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 30px 35px;
        max-width: 650px;
        margin: 0 auto;
        border: 1px solid #eef1ef;
        border-top: 4px solid #1A8969;
    }

    .form-card h2 {
        color: #1A8969;
        font-size: 20px;
        margin-bottom: 4px;
    }

    .form-subtitle {
        color: #888;
        font-size: 13px;
        margin-bottom: 22px;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #444;
    }

    input[type="text"] {
        width: 100%;
        padding: 11px 13px;
        border: 1.3px solid #ddd;
        border-radius: 9px;
        font-size: 14px;
        color: #333;
        transition: 0.2s ease;
        background: #fafafa;
        margin-bottom: 0;
    }

    input[type="text"]:focus {
        outline: none;
        border-color: #1A8969;
        background: white;
        box-shadow: 0 0 0 3px rgba(26, 137, 105, 0.12);
    }

    .error {
        color: #d9534f;
        font-size: 12px;
        margin-top: 6px;
        margin-bottom: 8px;
    }

    .buttons {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        border-top: 1px solid #f0f0f0;
        padding-top: 20px;
    }

    .svg { width: 16px; height: 16px; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 22px;
        border-radius: 20px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        transition: 0.2s ease;
    }

    .btn-save {
        background: #1A8969;
        color: white;
        box-shadow: 0 2px 8px rgba(26, 137, 105, 0.3);
    }
    .btn-save:hover {
        background: #146e54;
        box-shadow: 0 4px 12px rgba(26, 137, 105, 0.4);
        transform: translateY(-1px);
    }

    .btn-cancel {
        background: white;
        color: #777;
        border: 1.3px solid #ccc;
    }
    .btn-cancel:hover {
        background: #f5f5f5;
        border-color: #999;
    }

    @media (max-width: 500px) {
        .form-card { padding: 22px 18px; }
        .buttons { flex-direction: column-reverse; }
        .btn { width: 100%; }
    }
</style>

<div class="form-card">
    <h2>Ajouter une direction</h2>
    <p class="form-subtitle">Créez la direction. Ses services s'ajoutent ensuite dans la page « Services ».</p>

    <form method="POST" action="{{ route('admin.directions.store') }}">
        @csrf

        <label for="nom_direction">Nom de la direction</label>
        <input type="text" id="nom_direction" name="nom_direction" value="{{ old('nom_direction') }}" placeholder="Exemple : Direction des Impôts" required autofocus>
        @error('nom_direction') <div class="error">{{ $message }}</div> @enderror

        <div class="buttons">
            <button type="submit" class="btn btn-save"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg> Enregistrer</button>
            <a href="{{ route('admin.directions.index') }}" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>
@endsection