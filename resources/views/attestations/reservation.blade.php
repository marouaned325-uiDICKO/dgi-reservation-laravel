<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation {{ $r->reference }}</title>
    <style>
        @page { margin: 34px 40px 60px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11.5px; color: #222; }

        .entete { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .entete td { vertical-align: middle; }
        .entete .logo { width: 80px; }
        .entete .logo img { width: 64px; }
        .entete .org { text-align: center; }
        .entete .org .nom { font-size: 13px; font-weight: bold; color: #1A8969; text-transform: uppercase; letter-spacing: 0.5px; }
        .entete .org .pays { font-size: 10.5px; color: #777; margin-top: 2px; }
        .filet { height: 3px; background: #D4AF37; margin: 8px 0 22px; }

        h1 { font-size: 17px; color: #1A8969; text-align: center; margin: 0 0 4px; }
        .sous-titre { text-align: center; font-size: 11.5px; color: #555; margin-bottom: 22px; }

        .section { font-size: 12px; font-weight: bold; color: #1a1a1a; border-bottom: 1px solid #cfd8d3; padding-bottom: 4px; margin: 20px 0 8px; }

        table.infos { width: 100%; border-collapse: collapse; }
        table.infos td { border: 1px solid #cfd8d3; padding: 7px 9px; font-size: 11px; vertical-align: top; }
        table.infos td.lib { background: #e9f1ee; font-size: 9.5px; font-weight: bold; color: #444; text-transform: uppercase; letter-spacing: 0.3px; width: 19%; }
        table.infos td.val { width: 31%; }

        .statut { display: inline-block; background: #d4edda; color: #155724; font-weight: bold; font-size: 10.5px; padding: 2px 10px; }

        .texte { margin-top: 18px; line-height: 1.6; font-size: 11px; color: #333; }

        table.signatures { width: 100%; border-collapse: collapse; margin-top: 34px; }
        table.signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 11px; padding: 0 20px; }
        table.signatures .espace { height: 56px; border-bottom: 1px solid #555; }
        table.signatures .nom { margin-top: 6px; font-size: 10.5px; color: #444; }

        .pied { position: fixed; left: 0; right: 0; bottom: -34px; border-top: 1px solid #cfd8d3; padding-top: 6px; text-align: center; font-size: 9px; color: #888; }
    </style>
</head>
<body>

    <table class="entete">
        <tr>
            <td class="logo">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo">
                @endif
            </td>
            <td class="org">
                <div class="nom">Direction Générale des Impôts</div>
                <div class="pays">Burkina Faso</div>
            </td>
            <td class="logo"></td>
        </tr>
    </table>
    <div class="filet"></div>

    <h1>Attestation de validation de réservation de salle</h1>
    <div class="sous-titre">Réservation {{ $r->reference }}</div>

    <div class="section">Demandeur</div>
    <table class="infos">
        <tr>
            <td class="lib">Nom et prénom</td>
            <td class="val">{{ $demandeur->name ?? 'Utilisateur supprimé' }}</td>
            <td class="lib">Référence agent</td>
            <td class="val">{{ $demandeur->reference ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lib">Direction</td>
            <td class="val">{{ $direction }}</td>
            <td class="lib">Service</td>
            <td class="val">{{ $service }}</td>
        </tr>
        <tr>
            <td class="lib">Email</td>
            <td class="val" colspan="3">{{ $r->email ?? ($demandeur->email ?? '-') }}</td>
        </tr>
    </table>

    <div class="section">Salle réservée</div>
    <table class="infos">
        <tr>
            <td class="lib">Salle</td>
            <td class="val">{{ $salle->nom ?? 'Salle supprimée' }}</td>
            <td class="lib">Référence salle</td>
            <td class="val">{{ $salle->reference ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lib">Localité</td>
            <td class="val">{{ $salle->localite ?? '-' }}</td>
            <td class="lib">Capacité</td>
            <td class="val">{{ $salle->capacite ?? '-' }} places</td>
        </tr>
        <tr>
            <td class="lib">Direction de la salle</td>
            <td class="val" colspan="3">{{ $salle->direction->nom_direction ?? '-' }}</td>
        </tr>
    </table>

    <div class="section">Réservation</div>
    <table class="infos">
        <tr>
            <td class="lib">Motif</td>
            <td class="val">{{ $r->motif }}</td>
            <td class="lib">Participants</td>
            <td class="val">{{ $r->nombre ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lib">Période</td>
            <td class="val">{{ $periode }}</td>
            <td class="lib">Horaire</td>
            <td class="val">{{ $horaire }}</td>
        </tr>
        <tr>
            <td class="lib">Demande déposée le</td>
            <td class="val">{{ $r->created_at->format('d/m/Y à H:i') }}</td>
            <td class="lib">Statut</td>
            <td class="val"><span class="statut">Confirmée</span></td>
        </tr>
    </table>

    <div class="section">Validation</div>
    <table class="infos">
        <tr>
            <td class="lib">Validée par</td>
            <td class="val">{{ $valideur->name ?? 'Non renseigné' }}@if ($roleValideur) ({{ $roleValideur }})@endif</td>
            <td class="lib">Date de validation</td>
            <td class="val">{{ $dateValidation }}</td>
        </tr>
    </table>

    <div class="texte">
        La Direction Générale des Impôts atteste que la demande de réservation référencée
        <strong>{{ $r->reference }}</strong> a été examinée et <strong>validée</strong>.
        La salle <strong>{{ $salle->nom ?? '-' }}</strong> est réservée au demandeur pour la période et
        l'horaire indiqués ci-dessus.
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div>Le demandeur</div>
                <div class="espace"></div>
                <div class="nom">{{ $demandeur->name ?? '' }}</div>
            </td>
            <td>
                <div>Le valideur</div>
                <div class="espace"></div>
                <div class="nom">{{ $valideur->name ?? '' }}</div>
            </td>
        </tr>
    </table>

    <div class="pied">
        Document généré le {{ $genereLe }} par la plateforme de gestion des salles de la DGI &middot; {{ $r->reference }}
    </div>

</body>
</html>