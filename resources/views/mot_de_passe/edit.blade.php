@extends('layouts.dgi')

@section('title', 'Paramètres - DGI')

@section('content')
<style>
    .parametres { max-width: 560px; margin: 0 auto; }
    .parametres h2 { color: #1A8969; font-size: 20px; margin-bottom: 18px; }

    .carte { background: white; border-radius: 10px; padding: 22px 26px; margin-bottom: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .carte h3 { font-size: 15px; color: #1a1a1a; margin-bottom: 4px; }
    .carte .aide { font-size: 13px; color: #777; margin-bottom: 18px; line-height: 1.5; }

    .profil { display: grid; grid-template-columns: 110px 1fr; gap: 8px 12px; font-size: 13px; }
    .profil .cle { color: #888; }
    .profil .valeur { color: #222; font-weight: 600; }

    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 6px; font-size: 13px; color: #555; }
    input { width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
    .error { color: #d9534f; font-size: 12px; margin-top: 4px; }
    .message { padding: 10px 14px; margin-bottom: 18px; border-radius: 6px; background: #dff0d8; color: #3c763d; font-size: 14px; }
    .btn-save { width: 100%; padding: 10px; background: #1A8969; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
    .btn-save:hover { background: #17492d; }
</style>

<div class="parametres">
    <h2>⚙️ Paramètres</h2>

    @if (session('success'))
        <div class="message">{{ session('success') }}</div>
    @endif

    <div class="carte">
        <h3>Mon profil</h3>
        <p class="aide">Ces informations sont gérées par l'administrateur.</p>

        <div class="profil">
            <span class="cle">Nom</span>
            <span class="valeur">{{ auth()->user()->name }}</span>

            <span class="cle">Email</span>
            <span class="valeur">{{ auth()->user()->email }}</span>

            <span class="cle">Direction</span>
            <span class="valeur">{{ auth()->user()->direction ?? '-' }}</span>

            <span class="cle">Service</span>
            <span class="valeur">{{ auth()->user()->service ?? '-' }}</span>

            <span class="cle">Rôle</span>
            <span class="valeur">
                @if (auth()->user()->role === 'admin') Administrateur
                @elseif (auth()->user()->role === 'gestionnaire') Gestionnaire
                @else Agent demandeur
                @endif
            </span>
        </div>
    </div>

    <div class="carte">
        <h3>🔑 Sécurité : changer mon mot de passe</h3>
        <p class="aide">Choisissez un mot de passe personnel que vous êtes le seul à connaître (8 caractères minimum).</p>

        <form method="POST" action="{{ route('mot_de_passe.update') }}" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Mot de passe actuel</label>
                <input type="password" name="mot_de_passe_actuel" autocomplete="current-password">
                @error('mot_de_passe_actuel') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="password" autocomplete="new-password">
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Confirmer le nouveau mot de passe</label>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </div>

            <button type="submit" class="btn-save">Enregistrer le nouveau mot de passe</button>
        </form>
    </div>
</div>
@endsection