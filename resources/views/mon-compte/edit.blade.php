@extends('layouts.dgi')

@section('title', 'Modifier mon compte - DGI')

@section('content')
<style>
    .form-card { background: white; border-radius: 14px; padding: 28px 32px; max-width: 620px; margin: 0 auto; border: 1px solid #eef1ef; border-top: 4px solid #1A8969; }
    .form-card h2 { color: #1A8969; font-size: 20px; margin-bottom: 20px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 16px; }
    label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #444; }
    input, select { width: 100%; padding: 11px 13px; border: 1.3px solid #ddd; border-radius: 9px; font-size: 14px; background: #fafafa; font-family: inherit; }
    input:focus, select:focus { outline: none; border-color: #1A8969; background: white; box-shadow: 0 0 0 3px rgba(26,137,105,.12); }
    input:disabled, select:disabled { background: #f0f2f1; color: #777; cursor: not-allowed; }
    .error { color: #d9534f; font-size: 12px; margin-top: 5px; }
    .buttons { display: flex; gap: 10px; margin-top: 8px; }
    .btn { padding: 11px 26px; border-radius: 20px; text-decoration: none; border: none; cursor: pointer; font-size: 13px; font-weight: 700; font-family: inherit; }
    .btn-save { background: #1A8969; color: white; }
    .btn-save:hover { background: #146e54; }
    .btn-cancel { background: white; color: #777; border: 1.3px solid #ccc; }
    .btn-cancel:hover { background: #f5f5f5; }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } .form-card { padding: 20px; } }
</style>

<div class="form-card">
    <h2>Modifier l'utilisateur</h2>

    <form method="POST" action="{{ route('mon-compte.mettre-a-jour') }}" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nom et prénom</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}" maxlength="8" inputmode="numeric" placeholder="70000000" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 8);">
                @error('telephone') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        @if ($user->role !== 'admin')
            <div class="form-row">
                <div class="form-group">
                    <label>Direction</label>
                    <select disabled>
                        <option>{{ $user->direction->nom_direction ?? '-' }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Service</label>
                    <select disabled>
                        <option>{{ $user->service->nom_service ?? '-' }}</option>
                    </select>
                </div>
            </div>
        @endif

        <div class="buttons">
            <button type="submit" class="btn btn-save">Enregistrer</button>
            <a href="/mon-compte" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>
@endsection