<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotifSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $motifs = [
            'CONFÉRENCE', 'RÉUNION', 'FORMATION', 'ATELIER',
            'CONTRÔLE', 'AUDIT', 'INTERVENTION', 'SÉMINAIRE',
        ];

        foreach (DB::table('directions')->pluck('id') as $directionId) {
            foreach ($motifs as $nom) {
                DB::table('motifs')->updateOrInsert(
                    ['direction_id' => $directionId, 'nom' => $nom],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}