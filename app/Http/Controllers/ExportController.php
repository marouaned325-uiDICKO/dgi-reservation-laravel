<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    /**
     * Génère un fichier Excel à partir du tableau affiché à l'écran
     * (en-têtes + lignes déjà filtrées et triées côté navigateur).
     */
    public function tableau(Request $request)
    {
        $data = $request->validate([
            'titre'   => 'required|string|max:60',
            'entetes' => 'required|string',
            'lignes'  => 'required|string',
        ]);

        $entetes = json_decode($data['entetes'], true);
        $lignes = json_decode($data['lignes'], true);

        abort_unless(is_array($entetes) && is_array($lignes), 422, 'Données invalides.');
        abort_if(count($entetes) === 0 || count($entetes) > 30 || count($lignes) > 5000, 422, 'Export trop volumineux.');

        $spreadsheet = new Spreadsheet();
        $feuille = $spreadsheet->getActiveSheet();

        // Nom d'onglet : 31 caractères maximum, sans caractères interdits par Excel
        $nomOnglet = trim(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $data['titre']));
        $feuille->setTitle(mb_substr($nomOnglet !== '' ? $nomOnglet : 'Export', 0, 31));

        // Toutes les valeurs sont écrites comme du texte (aucune formule exécutée)
        foreach (array_values($entetes) as $c => $valeur) {
            $feuille->setCellValueExplicit([$c + 1, 1], mb_substr((string) $valeur, 0, 500), DataType::TYPE_STRING);
        }

        foreach (array_values($lignes) as $r => $ligne) {
            foreach (array_values((array) $ligne) as $c => $valeur) {
                if ($c >= count($entetes)) {
                    break;
                }
                $feuille->setCellValueExplicit([$c + 1, $r + 2], mb_substr((string) $valeur, 0, 1000), DataType::TYPE_STRING);
            }
        }

        $derniereColonne = Coordinate::stringFromColumnIndex(count($entetes));
        $style = $feuille->getStyle("A1:{$derniereColonne}1");
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1A8969');
        $feuille->freezePane('A2');

        for ($i = 1; $i <= count($entetes); $i++) {
            $feuille->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $nomFichier = Str::slug($data['titre'], '_') . '_' . now()->format('Y-m-d_Hi') . '.xlsx';
        $ecrivain = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($ecrivain) {
            $ecrivain->save('php://output');
        }, $nomFichier, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}