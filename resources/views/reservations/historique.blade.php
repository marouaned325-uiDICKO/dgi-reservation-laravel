@extends('layouts.dgi')

@section('title', 'Historique - DGI')

@section('content')
<style>
    table { width: 100%; background: white; border-radius: 8px; border-collapse: collapse; overflow: hidden; }
    th, td { padding: 10px 15px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f8f8f8; font-size: 13px; color: #666; }

    .badge { padding: 3px 10px; border-radius: 4px; font-size: 12px; }
    .badge-confirmee { background: #d4edda; color: #155724; }
    .badge-en_attente { background: #fff3cd; color: #856404; }
    .badge-refusee { background: #f8d7da; color: #721c24; }
    .badge-annulee { background: #eee; color: #666; }

    .empty { text-align: center; color: #666; padding: 40px; }
</style>

@php
    $estGestionnaire = auth()->user()->estGestionnaire() || auth()->user()->estAdmin();
@endphp

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Salle</th>
            <th>Motif</th>
            <th>Créneau</th>
            @if ($estGestionnaire)
            <th>Agent</th>
            @endif
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($demandes as $r)
        <tr>
            <td>{{ $r->date_reserv }}</td>
            <td>{{ $r->salle->nom }}</td>
            <td>{{ $r->motif }}</td>
            <td>{{ \Carbon\Carbon::parse($r->heure_debut)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->heure_fin)->format('H:i') }}</td>
            @if ($estGestionnaire)
            <td>{{ $r->utilisateur->name }}</td>
            @endif
            <td><span class="badge badge-{{ $r->statut }}">{{ ucfirst($r->statut) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="{{ $estGestionnaire ? 6 : 5 }}" class="empty">Aucun historique pour le moment.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection