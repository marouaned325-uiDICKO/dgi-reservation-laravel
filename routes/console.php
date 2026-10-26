<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('import:mysql {database} {--user=root} {--password=} {--port=3306}', function ($database) {    config(['database.connections.mysql_old' => [
        'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $this->option('port'),
        'database' => $database,
        'username' => $this->option('user'),
        'password' => $this->option('password') ?? '',
        'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '',
    ]]);

    $mysql = DB::connection('mysql_old');
    $pg = DB::connection('pgsql');

    $ignorer = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs',
                'job_batches', 'failed_jobs', 'password_reset_tokens'];

    $tables = collect($mysql->select('SHOW TABLES'))
        ->map(fn ($r) => array_values((array) $r)[0])
        ->reject(fn ($t) => in_array($t, $ignorer))
        ->filter(fn ($t) => Schema::connection('pgsql')->hasTable($t))
        ->values();

    if ($tables->isEmpty()) {
        $this->error('Aucune table commune trouvée. Vérifie le nom de la base MySQL.');
        return;
    }

    // Désactive les clés étrangères pendant l'import et vide les tables PostgreSQL
    $pg->statement("SET session_replication_role = 'replica'");
    $pg->statement('TRUNCATE TABLE ' . $tables->map(fn ($t) => "\"$t\"")->implode(', ') . ' RESTART IDENTITY CASCADE');

    foreach ($tables as $table) {
        $colonnes = collect(Schema::connection('pgsql')->getColumns($table))->keyBy('name');
        $lignes = $mysql->table($table)->get();

        $donnees = $lignes->map(function ($ligne) use ($colonnes) {
            $res = [];
            foreach ((array) $ligne as $col => $val) {
                if (!$colonnes->has($col)) continue; // colonne absente côté PostgreSQL
                if (is_string($val) && str_starts_with($val, '0000-00-00')) $val = null;
                if ($colonnes[$col]['type_name'] === 'bool' && $val !== null) $val = (bool) $val;
                $res[$col] = $val;
            }
            return $res;
        });

        foreach ($donnees->chunk(200) as $paquet) {
            $pg->table($table)->insert($paquet->values()->all());
        }

        // Recale le compteur d'ID
        if ($colonnes->has('id')) {
            $pg->statement("SELECT setval(pg_get_serial_sequence('\"$table\"', 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM \"$table\"");
        }

        $this->info("$table : {$lignes->count()} ligne(s) importée(s)");
    }

    $pg->statement("SET session_replication_role = 'origin'");
    $this->info('Import terminé.');
})->purpose('Copie les données de l\'ancienne base MySQL vers PostgreSQL');

Artisan::command('admin:creer {email} {--password=Admin@2026} {--role=}', function ($email) {
    $roles = DB::table('users')->distinct()->pluck('role')->filter()->values();
    $this->info('Rôles existants : ' . ($roles->implode(', ') ?: 'aucun'));

    $role = $this->option('role')
        ?: ($roles->isNotEmpty()
            ? $this->choice('Quel rôle donner au compte ?', $roles->all())
            : $this->ask('Nom du rôle', 'admin_systeme'));

    $existe = DB::table('users')->where('email', $email)->exists();

    $data = [
        'password'   => Hash::make($this->option('password')),
        'role'       => $role,
        'updated_at' => now(),
    ];

    if (!$existe) {
        $data['created_at'] = now();
        foreach (['nom' => 'Admin', 'prenom' => 'Système', 'name' => 'Admin Système', 'telephone' => '00000000'] as $col => $val) {
            if (Schema::hasColumn('users', $col)) $data[$col] = $val;
        }
    }
    if (Schema::hasColumn('users', 'doit_changer_mot_de_passe')) $data['doit_changer_mot_de_passe'] = false;
    if (Schema::hasColumn('users', 'email_verified_at')) $data['email_verified_at'] = now();

    DB::table('users')->updateOrInsert(['email' => $email], $data);

    $this->info(($existe ? 'Compte mis à jour' : 'Compte créé') . " : $email / mot de passe : " . $this->option('password') . " / rôle : $role");
})->purpose('Crée ou réinitialise un compte administrateur');