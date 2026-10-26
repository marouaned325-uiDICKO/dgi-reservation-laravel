<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Chaque salle appartient à une direction
        if (!Schema::hasColumn('salles', 'direction_id')) {
            Schema::table('salles', function (Blueprint $table) {
                $table->foreignId('direction_id')
                    ->nullable()
                    ->constrained('directions')
                    ->nullOnDelete();
            });
        }

        // 2. La colonne "role" devient un texte libre (accepte tous les rôles)
        DB::table('users')->whereNull('role')->update(['role' => 'agent']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(30)');
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'agent'");
            DB::statement('ALTER TABLE users ALTER COLUMN role SET NOT NULL');
        } else {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(30) NOT NULL DEFAULT 'agent'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('salles', 'direction_id')) {
            Schema::table('salles', function (Blueprint $table) {
                $table->dropConstrainedForeignId('direction_id');
            });
        }
    }
};