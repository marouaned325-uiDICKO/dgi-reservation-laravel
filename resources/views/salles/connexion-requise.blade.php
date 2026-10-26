@extends('layouts.public')

@section('title', 'Connexion requise - DGI')

@section('content')
<div style="display: flex; align-items: center; justify-content: center; min-height: 60vh; padding: 20px;">
    <div style="background: white; border-radius: 12px; padding: 40px; max-width: 420px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: ##1F6B41; margin-bottom: 10px;">Connexion requise</h2>
        <p style="color: #555; margin-bottom: 25px;">
            Pour réserver <strong>{{ $salle->nom }}</strong>, vous devez d'abord vous connecter ou créer un compte agent.
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="{{ route('login') }}" style="padding: 10px 20px; background: ##1F6B41; color: white; border-radius: 4px; text-decoration: none; font-size: 14px;">Se connecter</a>
            <a href="{{ route('register') }}" style="padding: 10px 20px; background: #f0f0f0; color: #333; border-radius: 4px; text-decoration: none; font-size: 14px;">Créer un compte</a>
        </div>
        <a href="{{ route('salles.galerie') }}" style="display: inline-block; margin-top: 20px; color: #666; font-size: 13px;">← Retour aux salles</a>
    </div>
</div>
@endsection