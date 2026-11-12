<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministrateurSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('dgi.admin.email');
        $password = config('dgi.admin.password');

        if (! $email || ! $password) {
            $this->command?->error('ADMIN_EMAIL et ADMIN_PASSWORD doivent être définis dans le .env');
            return;
        }

        $admin = User::firstOrNew(['email' => $email]);

        $admin->forceFill([
            'name'                      => config('dgi.admin.name'),
            'role'                      => 'admin',
            'est_principal'             => false,
            'direction_id'              => null,
            'service_id'                => null,
            'doit_changer_mot_de_passe' => false,
            'must_change_password'      => false,
            'password'                  => Hash::make($password),
        ])->save();

        $this->command?->info("Administrateur système prêt : {$email}");
    }
}