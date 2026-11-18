<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministrateurSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@dgi.bf';
        $password = 'Admin@2026';

        $admin = User::firstOrNew(['email' => $email]);

        $admin->forceFill([
            'name'                      => 'Administrateur Système',
            'role'                      => 'admin',
            'est_principal'             => true,
            'direction_id'              => null,
            'service_id'                => null,
            'doit_changer_mot_de_passe' => false,
            'must_change_password'      => false,
            'password'                  => Hash::make($password),
        ])->save();

        $this->command?->info("Administrateur système prêt : {$email}");
    }
}