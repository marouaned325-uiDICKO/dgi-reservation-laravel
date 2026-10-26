@extends('layouts.dgi')

@section('title', 'Localités - DGI')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .page-header h2 { color: #1A8969; font-size: 20px; }
    .btn { display: inline-block; padding: 9px 14px; border-radius: 4px; text-decoration: none; border: none; cursor: pointer; font-size: 13px; }
    .btn-add { background: #1A8969; color: white; }
    .btn-edit { background: #e6a700; color: white; }
    .btn-delete { background: #d9534f; color: white; }
    .ville-card { background: white; border-radius: 8px; padding: 18px 22px; margin-bottom: 14px; max-width: 700px; margin-left: auto; margin-right: auto; }
    .ville-card h3 { color: #1A8969; margin-bottom: 8px; }
    .secteurs-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .secteur-tag { background: #e8f5e9; color: #1A8969; padding: 4px 10px; border-radius: 20px; font-size: 12px; }
    .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; background: #dff0d8; color: #3c763d; max-width: 700px; margin-left: auto; margin-right: auto; }
</style>

<div class="page-header">
    <h2>Gestion des localités</h2>
    <a href="{{ route('admin.localites.create') }}" class="btn btn-add">+ Ajouter une localité</a>
</div>

@if(session('success'))
    <div class="message">{{ session('success') }}</div>
@endif

@forelse ($localites as $localite)
<div class="ville-card">
    <h3>{{ $localite->nom }}</h3>

    <div class="secteurs-tags">
        @forelse ($localite->secteurs as $secteur)
            <span class="secteur-tag">{{ $secteur->nom }}</span>
        @empty
            <span style="color:#999; font-size:13px;">Aucun secteur</span>
        @endforelse
    </div>

    <div style="display:flex; gap:8px;">
        <a href="{{ route('admin.localites.edit', $localite) }}" class="btn btn-edit">Modifier</a>
        <form method="POST" action="{{ route('admin.localites.destroy', $localite) }}" onsubmit="return confirm('Supprimer cette ville et tous ses secteurs ?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-delete">Supprimer</button>
        </form>
    </div>
</div>
@empty
<p style="text-align:center; color:#888;">Aucune localité enregistrée.</p>
@endforelse
@endsection