<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DirectionServiceSeeder::class,
            EquipementSeeder::class,
            LocaliteSecteurSeeder::class,
            MotifSeeder::class,
            AdministrateurSeeder::class,
        ]);
    }
}