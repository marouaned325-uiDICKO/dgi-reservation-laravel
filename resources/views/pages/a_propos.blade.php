@extends('layouts.dgi')

@section('title', 'À Propos - DGI')

@section('content')
<style>
    .card { background: white; border-radius: 8px; padding: 25px 30px; max-width: 700px; }
    .card h2 { color: #1A8969; margin-bottom: 15px; font-size: 18px; }
    .card p { margin-bottom: 12px; line-height: 1.6; color: #444; }
</style>
<div class="card">
    <h2>À propos de la plateforme</h2>
    <p>Cette plateforme a été développée dans le cadre d'un mémoire de fin d'études, afin de faciliter la gestion et la réservation des salles au sein de la Direction Générale des Impôts (DGI) du Burkina Faso.</p>
    <p>Elle permet aux agents de soumettre des demandes de réservation, aux gestionnaires de les valider, et assure un suivi en temps réel de la disponibilité des salles.</p>
</div>
@endsection