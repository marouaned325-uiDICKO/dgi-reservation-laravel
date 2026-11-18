<?php

namespace Database\Seeders;

/**
 * Lit le fichier des structures de la DGI (database/seeders/data/structures_dgi.csv)
 * et le transforme en directions, services et localités pour l'application.
 *
 * - Une « direction » = une structure de tête (direction régionale, direction centrale,
 *   direction des moyennes entreprises, DGE, guichet unique du foncier...).
 * - Un « service » = toute structure placée sous cette direction (à n'importe quel niveau).
 * - Les structures de la Direction générale sans service sont regroupées dans
 *   « DIRECTION GENERALE DES IMPOTS ».
 */
class StructuresDgi
{
    private const FICHIER = __DIR__ . '/data/structures_dgi.csv';

    private const DIRECTION_GENERALE = 'DIRECTION GENERALE DES IMPOTS';

    /** Noms mal saisis dans le fichier source, corrigés par code de structure. */
    private const NOMS_CORRIGES = [
        'DC07' => 'DIRECTION DES ENQUETES ET DES RECHERCHES FISCALES',
        'SE02' => 'DIRECTION REGIONALE DES IMPOTS DES HAUTS-BASSINS',
        '3'    => 'DIRECTION DES MOYENNES ENTREPRISES DES HAUTS-BASSINS',
    ];

    /** @return array{directions: array<string, string[]>, localites: string[]} */
    public static function charger(string $fichier = self::FICHIER): array
    {
        $lignes = self::lireLignes($fichier);

        // Index des structures par code (le premier gagne en cas de code en double)
        $parCode = [];
        foreach ($lignes as $l) {
            $parCode[$l['code']] ??= $l;
        }

        // Structure de tête d'une ligne : on remonte les « supervisé par » jusqu'au sommet
        $racine = function (array $l) use ($parCode): string {
            $courant = $l;
            for ($i = 0; $i < 10; $i++) {
                $p = $courant['parent'];
                if ($p === '' || $p === '0' || ! isset($parCode[$p])) {
                    return $courant['code'];
                }
                $courant = $parCode[$p];
            }
            return $courant['code'];
        };

        // Regroupe chaque structure sous sa structure de tête
        $enfants = [];   // code de la structure de tête => structures placées sous elle
        foreach ($lignes as $l) {
            $enfants[$racine($l)][] = $l;
        }

        $directions = [];
        $dg = [];

        foreach ($enfants as $codeRacine => $membres) {
            $codeRacine = (string) $codeRacine; // les clés numériques deviennent des entiers en PHP

            if ($codeRacine === '0') {
                continue; // la ligne « Direction générale des impôts » elle-même
            }

            $tete = $parCode[$codeRacine];
            $services = array_values(array_filter($membres, fn ($m) => (string) $m['code'] !== $codeRacine));

            if (count($services) === 0) {
                // structure de la Direction générale sans service : devient un service de la DG
                $dg[] = $tete;
                continue;
            }

            $nomDirection = self::NOMS_CORRIGES[$codeRacine] ?? $tete['libelle'];
            $directions[$nomDirection] = self::nommerServices($services, $parCode);
        }

        if ($dg) {
            $directions[self::DIRECTION_GENERALE] = self::nommerServices($dg, $parCode);
        }

        ksort($directions);

        $localites = array_values(array_unique(array_filter(array_map(fn ($l) => $l['localite'], $lignes))));
        sort($localites, SORT_LOCALE_STRING);

        return ['directions' => $directions, 'localites' => $localites];
    }

    /**
     * « LIBELLE (SIGLE) ». Quand deux structures d'une même direction portent le même nom
     * (ex. un « Service d'assiette » par centre des impôts), on ajoute le sigle de leur
     * structure de rattachement, puis le code si ce n'est pas encore assez.
     */
    private static function nommerServices(array $services, array $parCode = []): array
    {
        $base = [];
        foreach ($services as $s) {
            $nom = $s['libelle'];
            if ($s['sigle'] !== '' && mb_strtoupper($s['sigle']) !== mb_strtoupper($s['libelle'])) {
                $nom .= ' (' . $s['sigle'] . ')';
            }
            $base[] = [$nom, $s];
        }

        $nombre = array_count_values(array_column($base, 0));

        $noms = [];
        foreach ($base as [$nom, $s]) {
            if ($nombre[$nom] > 1) {
                $parent = $parCode[$s['parent']] ?? null;
                if ($parent && $parent['sigle'] !== '') {
                    $nom .= ' - ' . $parent['sigle'];
                }
            }
            if (isset($noms[$nom])) {
                $nom .= ' [' . $s['code'] . ']';
            }
            $noms[$nom] = $nom;
        }

        $liste = array_values($noms);
        sort($liste, SORT_LOCALE_STRING);

        return $liste;
    }

    private static function lireLignes(string $fichier): array
    {
        if (! is_file($fichier)) {
            throw new \RuntimeException("Fichier introuvable : {$fichier}");
        }

        $handle = fopen($fichier, 'r');
        $entete = fgetcsv($handle, 0, ';');
        $entete[0] = preg_replace('/^\xEF\xBB\xBF/', '', $entete[0]); // BOM éventuel
        $position = array_flip($entete);

        $lignes = [];
        while (($c = fgetcsv($handle, 0, ';')) !== false) {
            if (count($c) < count($entete)) {
                continue;
            }
            $lignes[] = [
                'code'     => self::nettoyer($c[$position['CODE_STR']]),
                'parent'   => self::nettoyer($c[$position['CODE_STR_EST_SUPERVISE_PAR']]),
                'sigle'    => self::nettoyer($c[$position['SIGLE_STR']]),
                'libelle'  => self::nettoyer($c[$position['LIBELLE_STR']]),
                'localite' => self::nettoyer($c[$position['LOCALITE']]),
            ];
        }
        fclose($handle);

        return $lignes;
    }

    private static function nettoyer(string $texte): string
    {
        $texte = str_replace(['¿', '’'], "'", $texte);

        return trim(preg_replace('/\s+/u', ' ', $texte));
    }
}