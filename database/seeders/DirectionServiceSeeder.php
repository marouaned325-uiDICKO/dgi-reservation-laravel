<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DirectionServiceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $structure = [
            'Direction Générale'                    => ['Secrétariat', 'Cabinet'],
            'Direction des Grandes Entreprises'     => ['Service de la Fiscalité', 'Service du Contrôle'],
            'Direction des Moyennes Entreprises'    => ['Service de la Fiscalité', 'Service du Recouvrement'],
            'Direction des Ressources Humaines'     => ['Service du Personnel', 'Service de la Formation'],
            "Direction des Systèmes d'Information"  => ['Service Développement', 'Service Réseaux et Maintenance'],
        ];

        foreach ($structure as $nomDirection => $services) {
            $direction = DB::table('directions')->where('nom_direction', $nomDirection)->first();

            $directionId = $direction
                ? $direction->id
                : DB::table('directions')->insertGetId([
                    'nom_direction' => $nomDirection,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

            foreach ($services as $nomService) {
                DB::table('services')->updateOrInsert(
                    ['nom_service' => $nomService, 'direction_id' => $directionId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}