<?php

namespace Database\Seeders;

use App\Models\Direction;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Directions et services de la DGI, lus dans database/seeders/data/structures_dgi.csv.
 * Peut être relancé sans créer de doublons.
 */
class DirectionServiceSeeder extends Seeder
{
    public function run(): void
    {
        $structures = StructuresDgi::charger();

        DB::transaction(function () use ($structures) {
            Model::unguarded(function () use ($structures) {
                foreach ($structures['directions'] as $nomDirection => $services) {
                    $direction = Direction::firstOrCreate(['nom_direction' => $nomDirection]);

                    foreach ($services as $nomService) {
                        Service::firstOrCreate([
                            'direction_id' => $direction->id,
                            'nom_service'  => $nomService,
                        ]);
                    }
                }
            });
        });

        $this->command?->info(count($structures['directions']) . ' directions et '
            . array_sum(array_map('count', $structures['directions'])) . ' services enregistrés.');
    }
}