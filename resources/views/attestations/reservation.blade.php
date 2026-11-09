<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation {{ $r->reference }}</title>
    <style>
        @page { margin: 34px 50px 70px 50px; }
        body { font-family: 'Helvetica', 'DejaVu Sans', sans-serif; font-size: 12.5px; color: #111; line-height: 1.65; }

        /* ===== Filigrane (logo très pâle au centre) ===== */
        .filigrane { position: fixed; top: 270px; left: 175px; width: 330px; opacity: 0.07; }

        /* ===== Cartouche vert (collé au coin haut droit) ===== */
        .cachet { position: fixed; top: -34px; right: -50px; width: 215px; background: #1A8969; color: #fff;
                  padding: 9px 11px; font-size: 9px; line-height: 1.5; }
        .cachet b { color: #FFE680; }

        /* ===== En-tête ===== */
        table.entete { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.entete td { vertical-align: top; }
        .logo-cell { width: 120px; }
        .logo-cell img { width: 95px; }
        .org { text-align: center; padding-right: 215px; padding-left: 0; }
        .org .pays { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .org .devise { font-size: 9.5px; font-style: italic; }
        .org .tiret { font-size: 8px; letter-spacing: 1px; line-height: 1.2; }
        .org .dgi { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-top: 3px; }

        /* ===== Titre ===== */
        .titre-wrap { margin: 52px 0 38px; text-align: center; }
        .titre { display: inline-block; border: 2px solid #111; border-radius: 12px;
                 padding: 14px 40px; font-size: 20px; font-weight: bold; text-transform: uppercase; background: #fff; }

        /* ===== Corps ===== */
        .corps { padding: 0 6px; }
        .corps p { margin: 0 0 10px; }
        table.champs { border-collapse: collapse; margin: 2px 0 10px; width: 100%; }
        table.champs td { padding: 1px 0; vertical-align: top; }
        table.champs td.lib { width: 150px; }
        table.champs td.sep { width: 14px; }
        .fort { font-weight: bold; }

        /* ===== Signature ===== */
        .signature { margin-top: 20px; text-align: right; padding-right: 10px; }
        .signature .qui { margin-bottom: 40px; }
        .signature .nom { font-weight: bold; font-size: 11px; }
        .signature .role { font-style: italic; font-size: 10px; color: #333; }

        /* ===== Ampliations ===== */
        .ampl { margin-top: 26px; font-size: 12px; line-height: 1.5; }
        .ampl .t { font-weight: bold; text-decoration: underline; }
        .ampl ul { margin: 2px 0 0; padding-left: 16px; list-style: none; }

        /* ===== QR code ===== */
        .qr { position: fixed; right: 0; bottom: 62px; width: 78px; }
        .qr img { width: 78px; }

        /* ===== Bandeau de sécurité ===== */
        .secu { position: fixed; left: 0; right: 0; bottom: -28px; border: 1px solid #1A8969; }
        .secu .h { background: #1A8969; color: #fff; font-weight: bold; font-size: 9.5px; padding: 2px 7px; }
        .secu .b { padding: 4px 7px; font-size: 9px; line-height: 1.4; }
        .secu .code { color: #d40000; font-weight: bold; font-size: 11px; }

        /* ===== Étiquette en bas à droite ===== */
        .tag { position: fixed; right: 0; bottom: -52px; background: #000; color: #fff; font-size: 8.5px;
               font-weight: bold; padding: 1px 5px; text-transform: uppercase; }
    </style>
</head>
<body>

    @php
        $logoData = $logo ?? null;
    @endphp
    {{-- Filigrane --}}
    @if ($logoData)
<img class="filigrane" src="{{ $logoData }}" alt="">
    @endif

    {{-- Cartouche vert --}}
    <div class="cachet">
        Attestation générée par la plateforme<br>
        de gestion des salles de la Direction Générale des Impôts<br>
        <b>Délivrée le</b> {{ $genereLe }}<br>
        <b>N° de référence :</b> {{ $r->reference }}
    </div>

    {{-- En-tête --}}
    <table class="entete">
        <tr>
            <td class="logo-cell">
                @if ($logoData)
                    <img src="{{ $logoData }}" alt="Logo">
                @endif
            </td>
            <td class="org">
                <div class="pays">Burkina Faso</div>
                <div class="devise">La Patrie ou la Mort, nous Vaincrons</div>
                <div class="tiret">- - - - - - - - - -</div>
                <div class="dgi">Direction Générale des Impôts</div>
                <div class="tiret">- - - - - - - - - -</div>
            </td>
        </tr>
    </table>

    {{-- Titre --}}
    <div class="titre-wrap">
        <div class="titre">Attestation de réservation de salle</div>
    </div>

    {{-- Corps --}}
    <div class="corps">
        <p>
            Je soussigné, <span class="fort">{{ $valideur->name ?? 'le valideur' }}</span>@if ($roleValideur), {{ $roleValideur }}@endif,
            certifie que :
        </p>

        <table class="champs">
            <tr><td class="lib">Demandeur</td><td class="sep">:</td><td class="fort">{{ $demandeur->name ?? 'Utilisateur supprimé' }}</td></tr>
            <tr><td class="lib">Direction</td><td class="sep">:</td><td>{{ $direction ?: '-' }}</td></tr>
            <tr><td class="lib">Service</td><td class="sep">:</td><td>{{ $service ?: '-' }}</td></tr>
            <tr><td class="lib">Courriel</td><td class="sep">:</td><td>{{ $r->email ?? ($demandeur->email ?? '-') }}</td></tr>
        </table>

        <p>
            Bénéficiaire de la demande de réservation N° <span class="fort">{{ $r->reference }}</span>,
            déposée le {{ $r->created_at->format('d/m/Y') }} et validée le {{ $dateValidation }},
            pour le motif suivant : <span class="fort">{{ $r->motif }}</span>.
        </p>

        <p>
            Est autorisé à utiliser la salle <span class="fort">{{ $salle->nom ?? 'Salle supprimée' }}</span>
            @if (!empty($salle->localite)) ({{ $salle->localite }}@if (isset($salle->capacite)), {{ $salle->capacite }} places @endif)@endif,
            <span class="fort">{{ $periode }}</span>, de <span class="fort">{{ $horaire }}</span>
            @if (!empty($r->nombre)), pour {{ $r->nombre }} participant(s)@endif.
        </p>

        <p style="margin-top: 16px;">
            En foi de quoi, la présente attestation est établie pour servir et valoir ce que de droit.
        </p>
    </div>

    {{-- Signature --}}
    <div class="signature">
        <div class="qui">Gestionnaire de la salle</div>
        <div class="nom">{{ $valideur->name ?? '' }}</div>
        @if ($roleValideur)
            <div class="role">{{ $roleValideur }}</div>
        @endif
    </div>


    {{-- QR code (facultatif : affiché seulement si $qr est fourni) --}}
    @if (!empty($qr))
        <div class="qr"><img src="{{ $qr }}" alt="QR"></div>
    @endif

    {{-- Bandeau de sécurité --}}
    <div class="secu">
        <div class="h">CODE DE SÉCURITÉ</div>
        <div class="b">
            Cette attestation de réservation est authentifiable avec le code de sécurité suivant :
            <span class="code">{{ $r->reference }}</span><br>
            Pour vérifier l'authenticité et la validité de ce document, veuillez vous rapprocher du gestionnaire de la salle
            ou consulter la <b> plateforme de gestion des salles</b> de la DGI.
        </div>
    </div>

    <div class="tag">Attestation de réservation</div>

</body>
</html>