@extends('layouts.dgi')

@section('title', 'Changer mon mot de passe - DGI')

@section('content')
<style>
    .form-card { background: white; border-radius: 14px; padding: 28px 32px; max-width: 620px; margin: 0 auto; border: 1px solid #eef1ef; border-top: 4px solid #1A8969; }
    .form-card h2 { color: #1A8969; font-size: 20px; margin-bottom: 6px; }
    .form-card .sous-titre { color: #777; font-size: 13px; margin-bottom: 20px; }
    .form-group { margin-bottom: 16px; }
    label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #444; }
    input { width: 100%; padding: 11px 13px; border: 1.3px solid #ddd; border-radius: 9px; font-size: 14px; background: #fafafa; font-family: inherit; }
    input:focus { outline: none; border-color: #1A8969; background: white; box-shadow: 0 0 0 3px rgba(26,137,105,.12); }
    .error { color: #d9534f; font-size: 12px; margin-top: 5px; }
    .buttons { display: flex; gap: 10px; margin-top: 8px; }
    .btn { padding: 11px 26px; border-radius: 20px; text-decoration: none; border: none; cursor: pointer; font-size: 13px; font-weight: 700; font-family: inherit; }
    .btn-save { background: #1A8969; color: white; }
    .btn-save:hover { background: #146e54; }
    .btn-cancel { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-cancel:hover { background: #f5f5f5; }
</style>

<div class="form-card">
    <h2>Changer mon mot de passe</h2>
    <p class="sous-titre">Choisissez un mot de passe personnel de 8 caractères minimum.</p>

    <form method="POST" action="{{ route('mon-compte.mot-de-passe.update') }}" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="mot_de_passe_actuel">Mot de passe actuel</label>
            <input type="password" id="mot_de_passe_actuel" name="mot_de_passe_actuel" autocomplete="current-password" required>
            @error('mot_de_passe_actuel') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" autocomplete="new-password" required>
            @error('password') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
        </div>

        <div class="buttons">
            <button type="submit" class="btn btn-save">Enregistrer</button>
            <a href="/mon-compte" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>
@endsection