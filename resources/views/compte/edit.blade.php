@extends('layouts.dgi')

@section('title', 'Mon compte - DGI')

@section('content')
@php($utilisateur = auth()->user())

<style>
    .compte { max-width: 600px; margin: 0 auto; }
    .compte h2 { color: #1A8969; font-size: 20px; margin-bottom: 18px; }

    .carte { background: white; border-radius: 10px; padding: 22px 26px; margin-bottom: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .carte h3 { font-size: 15px; color: #1a1a1a; margin-bottom: 4px; }
    .carte .aide { font-size: 13px; color: #777; margin-bottom: 18px; line-height: 1.5; }

    .profil { display: grid; grid-template-columns: 140px 1fr; row-gap: 12px; font-size: 13px; }
    .profil .cle { color: #888; }
    .profil .valeur { color: #222; font-weight: 600; word-break: break-word; }

    .note-admin {
        margin-top: 18px; font-size: 12px; color: #999; font-style: italic;
    }
</style>

<div class="compte">
    <h2>Mon compte</h2>

    <div class="carte">
        <h3>Mon profil</h3>
        <p class="aide">Ces informations sont gérées par l'administrateur.</p>

        <div class="profil">
            <span class="cle">Nom complet</span>
            <span class="valeur">{{ $utilisateur->name }}</span>

            <span class="cle">Email</span>
            <span class="valeur">{{ $utilisateur->email }}</span>

            <span class="cle">Téléphone</span>
            <span class="valeur">{{ $utilisateur->telephone ?: '-' }}</span>

            <span class="cle">Rôle</span>
            <span class="valeur">{{ $utilisateur->libelle_role }}</span>

            @if ($utilisateur->role !== 'admin')
                <span class="cle">Direction</span>
                <span class="valeur">{{ $utilisateur->direction->nom_direction ?? '-' }}</span>

                <span class="cle">Service</span>
                <span class="valeur">{{ $utilisateur->service->nom_service ?? '-' }}</span>
            @endif

            <span class="cle">Compte créé le</span>
            <span class="valeur">{{ $utilisateur->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <p class="note-admin">Pour modifier vos informations personnelles, contactez un administrateur.</p>
    </div>
</div>
@endsection