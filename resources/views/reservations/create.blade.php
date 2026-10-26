@extends('layouts.dgi')

@section('title', 'Réserver une salle - DGI')

@section('content')
<style>
    .svg { width: 16px; height: 16px; flex-shrink: 0; }

    .form-card {
        background: white; border-radius: 14px; padding: 30px 36px;
        max-width: 620px; margin: 0 auto;
        border: 1px solid #eef1ef; border-top: 4px solid #1A8969;
    }
    .form-card h2 { font-size: 20px; margin-bottom: 4px; color: #1A8969; }
    .form-card .sous-titre { font-size: 13px; color: #888; margin-bottom: 8px; }

    .salle-recap {
        display: flex; align-items: center; gap: 14px;
        background: #f4faf7; border: 1px solid #d6ebe2; border-left: 5px solid #D4AF37;
        border-radius: 12px; padding: 14px 16px; margin: 18px 0 6px;
    }
    .salle-recap img {
        width: 88px; height: 62px; object-fit: cover; border-radius: 8px;
        border: 1px solid #e3ece7; flex-shrink: 0;
    }
    .salle-recap .pastille-salle {
        width: 44px; height: 44px; border-radius: 10px; background: #e8f5e9; color: #1A8969;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .salle-recap .pastille-salle .svg { width: 22px; height: 22px; }
    .salle-recap-label { font-size: 11px; font-weight: 700; color: #777; text-transform: uppercase; letter-spacing: 0.7px; }
    .salle-recap-nom { font-size: 18px; font-weight: 700; color: #1A8969; line-height: 1.25; }
    .salle-recap-infos { font-size: 13px; color: #555; margin-top: 2px; }

    .section-label {
        display: flex; align-items: center; gap: 10px;
        margin: 24px 0 14px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;
        font-size: 12px; font-weight: 700; color: #5b6b63;
        text-transform: uppercase; letter-spacing: 0.7px;
    }
    .section-label .pastille {
        width: 30px; height: 30px; border-radius: 8px; background: #e8f5e9; color: #1A8969;
        display: inline-flex; align-items: center; justify-content: center;
    }

    .form-card label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px; }
    .form-card label small { color: #999; font-weight: 400; }
    .form-card input, .form-card select {
        width: 100%; padding: 11px 13px; border: 1.3px solid #ddd; border-radius: 9px;
        font-size: 14px; background: #fafafa; font-family: inherit; transition: 0.2s ease;
    }
    .form-card input:focus, .form-card select:focus {
        outline: none; border-color: #1A8969; background: white;
        box-shadow: 0 0 0 3px rgba(26, 137, 105, 0.12);
    }

    .ligne-double { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px; }
    .error { color: #d9534f; font-size: 12px; margin-top: 5px; }

    .boutons-form { display: flex; gap: 10px; margin-top: 26px; border-top: 1px solid #f0f0f0; padding-top: 22px; }
    .btn-retour {
        flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        padding: 12px; background: white; color: #666; text-decoration: none;
        border: 1.3px solid #ccc; border-radius: 30px; font-size: 14px; font-weight: 700; transition: 0.2s;
    }
    .btn-retour:hover { background: #f5f5f5; border-color: #999; }
    .btn-envoyer {
        flex: 2; display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        padding: 12px; background: #1A8969; color: white; border: none; border-radius: 30px;
        cursor: pointer; font-size: 14px; font-weight: 700; font-family: inherit;
        box-shadow: 0 2px 8px rgba(26, 137, 105, 0.3); transition: 0.2s;
    }
    .btn-envoyer:hover { background: #146e54; transform: translateY(-1px); }

    @media (max-width: 560px) {
        .form-card { padding: 22px 18px; }
        .ligne-double { grid-template-columns: 1fr; gap: 14px; }
        .boutons-form { flex-direction: column-reverse; }
        .salle-recap img { width: 70px; height: 52px; }
    }
</style>

<div class="form-card">
    <h2>Réserver une salle</h2>
    <p class="sous-titre">Complétez les informations ci-dessous pour soumettre votre demande.</p>

    {{-- Salle choisie --}}
    <div class="salle-recap">
        @if ($salle->photo)
            <img src="{{ asset('storage/' . $salle->photo) }}" alt="{{ $salle->nom }}">
        @else
            <span class="pastille-salle">
                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
            </span>
        @endif
        <div>
            <div class="salle-recap-label">Salle à réserver</div>
            <div class="salle-recap-nom">{{ $salle->nom }}</div>
            <div class="salle-recap-infos">
                {{ $salle->localite ?? '-' }} &middot; Capacité : {{ $salle->capacite }} places
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('reservations.store') }}">
        @csrf
        <input type="hidden" name="salle_id" value="{{ $salleId }}">

        <div class="section-label">
            <span class="pastille"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></span>
            Détails de la demande
        </div>

        <div class="ligne-double">
            <div>
                <label for="motif">Motif de la réservation</label>
                <select id="motif" name="motif" required>
                    <option value="">Sélectionner un motif</option>
                    @foreach ($motifs as $motif)
                        <option value="{{ $motif }}" @selected(old('motif') == $motif)>{{ $motif }}</option>
                    @endforeach
                </select>
                @error('motif') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="nombre">Nombre de participants</label>
                <input type="number" id="nombre" name="nombre" min="1" max="{{ $salle->capacite }}" value="{{ old('nombre') }}" placeholder="Ex : 20">
                @error('nombre') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="section-label">
            <span class="pastille"><svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg></span>
            Période et horaire
        </div>

        <div class="ligne-double">
            <div>
                <label for="date_debut">Date début</label>
                <input type="date" id="date_debut" name="date_debut" value="{{ old('date_debut') }}" required>
                @error('date_debut') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="date_fin">Date fin</label>
                <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}" required>
                @error('date_fin') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="ligne-double">
            <div>
                <label for="heure_debut">Heure début <small>(facultatif)</small></label>
                <input type="time" id="heure_debut" name="heure_debut" value="{{ old('heure_debut') }}">
                @error('heure_debut') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="heure_fin">Heure fin <small>(facultatif)</small></label>
                <input type="time" id="heure_fin" name="heure_fin" value="{{ old('heure_fin') }}">
                @error('heure_fin') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="boutons-form">
            @auth
                <a href="{{ route('salles.liste') }}" class="btn-retour">
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg> Retour
                </a>
            @else
                <a href="{{ url('/') }}" class="btn-retour">
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg> Retour
                </a>
            @endauth

            <button type="submit" class="btn-envoyer">
                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4z"/></svg> Envoyer la demande
            </button>
        </div>
    </form>
</div>
@endsection