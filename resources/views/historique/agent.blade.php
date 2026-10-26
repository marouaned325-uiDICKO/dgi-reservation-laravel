@extends('layouts.dgi')

@section('title', 'Mon historique - DGI')

@section('content')
@php
    $libellesStatut = ['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'refusee' => 'Refusée', 'annulee' => 'Annulée'];
@endphp
<style>
    table { width: 100%; background: white; border-radius: 8px; border-collapse: collapse; overflow: hidden; }
    th, td { padding: 10px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
    th { background: #1A8969; color: white; font-size: 12px; }

    .badge { padding: 3px 10px; border-radius: 4px; font-size: 12px; }
    .badge-confirmee { background: #d4edda; color: #155724; }
    .badge-en_attente { background: #fff3cd; color: #856404; }
    .badge-refusee { background: #f8d7da; color: #721c24; }
    .badge-annulee { background: #eee; color: #666; }

    .empty { text-align: center; color: #666; padding: 40px; }
</style>

<div style="background: white; border-radius: 8px; overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Salle</th>
                <th>Motif</th>
                <th>Période</th>
                <th>Horaire</th>
                <th>Participants</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($demandes as $r)
            <tr>
                <td>{{ $r->created_at->format('d/m/Y') }}</td>
                <td>{{ $r->salle->nom ?? 'Salle supprimée' }} ({{ $r->salle->localite ?? '-' }})</td>
                <td>{{ $r->motif }}</td>
                <td>
                    @if ($r->date_fin && $r->date_fin != $r->date_reserv)
                        {{ \Carbon\Carbon::parse($r->date_reserv)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($r->date_fin)->format('d/m/Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($r->date_reserv)->format('d/m/Y') }}
                    @endif
                </td>
                <td>{{ $r->heure_debut ? substr($r->heure_debut, 0, 5) . ($r->heure_fin ? ' - ' . substr($r->heure_fin, 0, 5) : '') : 'Journée entière' }}</td>
                <td>{{ $r->nombre }}</td>
                <td>
                    <span class="badge badge-{{ $r->statut }}">{{ $libellesStatut[$r->statut] ?? ucfirst(str_replace('_', ' ', $r->statut)) }}</span>
                    @if ($r->statut === 'refusee' && $r->motif_refus)
                        <div style="font-size: 11px; color: #999; margin-top: 4px;">{{ $r->motif_refus }}</div>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty">Aucun historique pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection