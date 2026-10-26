@php
    $r = $reservation;
    $ok = $type === 'confirmation';
@endphp
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; color: #222;">
    <h2 style="color: {{ $ok ? '#1b7a3a' : '#b42318' }};">
        {{ $ok ? 'Réservation confirmée' : 'Réservation refusée' }}
    </h2>

    <p>Bonjour {{ $r->utilisateur->name ?? $r->utilisateur->nom ?? '' }},</p>

    <p>{{ $texte }}</p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 4px 12px 4px 0;"><strong>Salle</strong></td>
            <td>{{ $r->salle->nom }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0;"><strong>Date</strong></td>
            <td>{{ $r->date_reserv->format('d/m/Y') }}@if($r->date_fin && $r->date_fin != $r->date_reserv) au {{ \Carbon\Carbon::parse($r->date_fin)->format('d/m/Y') }}@endif</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0;"><strong>Horaire</strong></td>
            <td>
                @if($r->heure_debut && $r->heure_fin)
                    {{ substr($r->heure_debut, 0, 5) }} – {{ substr($r->heure_fin, 0, 5) }}
                @else
                    Journée entière
                @endif
            </td>
        </tr>
    </table>

    <p>Vous pouvez consulter le détail de vos réservations sur la plateforme.</p>

    <p style="margin-top: 24px;">Cordialement,<br>Direction Générale des Impôts</p>
</div>