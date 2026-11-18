<?php

namespace Database\Seeders;

use App\Models\Localite;
use App\Models\Secteur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Localités (villes) où la DGI est présente, lues dans le même fichier que les directions,
 * et secteurs des deux grandes villes. Peut être relancé sans créer de doublons.
 */
class LocaliteSecteurSeeder extends Seeder
{
    public function run(): void
    {
        $structures = StructuresDgi::charger();

        DB::transaction(function () use ($structures) {
            Model::unguarded(function () use ($structures) {
                foreach ($structures['localites'] as $nom) {
                    Localite::firstOrCreate(['nom' => $nom]);
                }

                // Ouagadougou : 12 arrondissements — Bobo-Dioulasso : 7 arrondissements
                foreach (['Ouagadougou' => 12, 'Bobo-Dioulasso' => 7] as $ville => $nombre) {
                    $localite = Localite::where('nom', $ville)->first();

                    if (! $localite) {
                        continue;
                    }

                    for ($i = 1; $i <= $nombre; $i++) {
                        Secteur::firstOrCreate([
                            'localite_id' => $localite->id,
                            'nom'         => "Arrondissement {$i}",
                        ]);
                    }
                }
            });
        });

        $this->command?->info(count($structures['localites']) . ' localités enregistrées.');
    }
}