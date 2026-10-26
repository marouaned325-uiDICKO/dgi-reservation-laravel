@extends('layouts.dgi')

@section('title', 'Notifications - DGI')

@section('content')
<style>
    .svg { width: 20px; height: 20px; flex-shrink: 0; }

    .page-header { max-width: 700px; margin-bottom: 18px; }
    .page-header h2 { color: #1A8969; font-size: 20px; }
    .page-header .sous-titre { color: #888; font-size: 13px; margin-top: 3px; }

    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 700px;
    }

    .notif-card {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
        border: 1px solid #eef1ef;
        border-left: 4px solid #ccc;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .notif-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }

    .notif-card.confirmation { border-left-color: #1A8969; }
    .notif-card.refus { border-left-color: #d9534f; }
    .notif-card.annulation { border-left-color: #999; }
    .notif-card.rappel { border-left-color: #D4AF37; }

    .notif-icone {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .notif-card.confirmation .notif-icone { background: #e8f5e9; color: #1A8969; }
    .notif-card.refus .notif-icone { background: #fdeaea; color: #d9534f; }
    .notif-card.annulation .notif-icone { background: #f0f0f0; color: #777; }
    .notif-card.rappel .notif-icone { background: #fdf3d9; color: #b8860b; }

    .notif-contenu { flex: 1; min-width: 0; }

    .notif-titre {
        font-weight: 700;
        font-size: 14px;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .notif-message {
        font-size: 14px;
        color: #444;
        line-height: 1.5;
        margin-bottom: 8px;
        word-break: break-word;
    }

    .notif-date {
        font-size: 12px;
        color: #999;
    }

    .empty {
        text-align: center;
        color: #888;
        padding: 60px 20px;
        background: white;
        border: 1px solid #eef1ef;
        border-radius: 12px;
        max-width: 700px;
    }
    .empty .icone-vide { color: #c5cfca; margin-bottom: 10px; }
    .empty .icone-vide .svg { width: 40px; height: 40px; }

    @media (max-width: 500px) {
        .notif-card { padding: 14px; gap: 12px; }
    }
</style>

<div class="page-header">
    <h2>Notifications</h2>
    <div class="sous-titre">{{ $notifications->count() }} notification(s)</div>
</div>

<div class="notif-list">
    @forelse ($notifications as $n)
        @php
            $type = in_array($n->type, ['confirmation', 'refus', 'annulation'], true) ? $n->type : 'rappel';
            $titres = [
                'confirmation' => 'Réservation confirmée',
                'refus'        => 'Réservation refusée',
                'annulation'   => 'Réservation annulée',
                'rappel'       => 'Rappel',
            ];
        @endphp
        <div class="notif-card {{ $type }}">
            <div class="notif-icone">
                @if ($type === 'confirmation')
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                @elseif ($type === 'refus')
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                @elseif ($type === 'annulation')
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg>
                @else
                    <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                @endif
            </div>

            <div class="notif-contenu">
                <div class="notif-titre">{{ $titres[$type] }}</div>
                <div class="notif-message">{{ $n->message }}</div>
                <div class="notif-date">{{ $n->date_env->format('d/m/Y à H:i') }}</div>
            </div>
        </div>
    @empty
        <div class="empty">
            <div class="icone-vide">
                <svg class="svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            </div>
            <p>Aucune notification pour le moment.</p>
        </div>
    @endforelse
</div>
@endsection