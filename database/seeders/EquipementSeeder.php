<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipementSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $equipements = [
            'Vidéoprojecteur', 'Écran', 'Climatisation', 'Ventilateurs', 'Tableau blanc',
            'Paperboard', 'Micro et sonorisation', 'Wi-Fi', 'Visioconférence',
            'Ordinateur', 'Prises électriques',
        ];

        foreach (DB::table('directions')->pluck('id') as $directionId) {
            foreach ($equipements as $nom) {
                DB::table('equipements')->updateOrInsert(
                    ['direction_id' => $directionId, 'nom' => $nom],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}