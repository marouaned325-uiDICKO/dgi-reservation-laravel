@extends('layouts.dgi')

@section('title', 'Demande envoyée - DGI')

@section('content')
<style>
    .carte-succes {
        background: white; border-radius: 14px; padding: 40px 30px;
        max-width: 450px; margin: 40px auto; text-align: center;
        border: 1px solid #eef1ef; border-top: 4px solid #D4AF37;
    }
    .carte-succes .icone {
        width: 60px; height: 60px; background: #e8f5e9; color: #1A8969;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
    }
    .carte-succes .icone svg { width: 28px; height: 28px; }
    .carte-succes h2 { font-size: 19px; color: #1A8969; margin-bottom: 10px; }
    .carte-succes p { color: #666; font-size: 14px; line-height: 1.5; margin-bottom: 25px; }
    .carte-succes .actions { display: flex; gap: 10px; }
    .carte-succes .actions a {
        flex: 1; padding: 11px; border-radius: 30px; text-decoration: none;
        font-size: 13px; font-weight: 700; text-align: center; transition: .2s ease;
    }
    .btn-primaire { background: #1A8969; color: white; box-shadow: 0 2px 8px rgba(26,137,105,.3); }
    .btn-primaire:hover { background: #146e54; }
    .btn-secondaire { background: white; color: #666; border: 1.3px solid #ccc; }
    .btn-secondaire:hover { background: #f5f5f5; }

    @media (max-width: 480px) { .carte-succes .actions { flex-direction: column; } }
</style>

<div class="carte-succes">
    <div class="icone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <h2>Demande envoyée</h2>
    <p>Votre demande de réservation a bien été enregistrée. Vous recevrez une notification dès qu'elle sera traitée.</p>
    <div class="actions">
        <a href="{{ route('salles.liste') }}" class="btn-secondaire">Nouvelle réservation</a>
        <a href="{{ route('reservations.mes_demandes') }}" class="btn-primaire">Mes demandes</a>
    </div>
</div>
@endsection