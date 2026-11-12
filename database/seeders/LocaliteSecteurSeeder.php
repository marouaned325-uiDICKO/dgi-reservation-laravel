<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocaliteSecteurSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $localites = [
            'Ouagadougou'    => [],
            'Bobo-Dioulasso' => [],
            'Koudougou'      => [],
            'Kaya'           => [],
            'Ouahigouya'     => [],
            'Banfora'        => [],
            "Fada N'Gourma"  => [],
            'Tenkodogo'      => [],
            'Dédougou'       => [],
            'Dori'           => [],
            'Gaoua'          => [],
            'Manga'          => [],
            'Ziniaré'        => [],
        ];

        foreach ($localites as $nom => $secteurs) {
            DB::table('localites')->updateOrInsert(
                ['nom' => $nom],
                ['created_at' => $now, 'updated_at' => $now]
            );

            $localiteId = DB::table('localites')->where('nom', $nom)->value('id');

            foreach ($secteurs as $secteur) {
                DB::table('secteurs')->updateOrInsert(
                    ['nom' => $secteur, 'localite_id' => $localiteId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}