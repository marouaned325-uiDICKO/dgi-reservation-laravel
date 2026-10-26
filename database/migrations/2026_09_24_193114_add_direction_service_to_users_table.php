<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'direction_id')) {
                $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            }
        });

        // Conversion des anciennes colonnes texte (seulement si elles existent)
        if (Schema::hasColumn('users', 'direction') && Schema::hasColumn('users', 'service')) {
            DB::table('users')->orderBy('id')->get()->each(function ($user) {
                $directionId = DB::table('directions')->where('nom_direction', $user->direction)->value('id');
                $serviceId = DB::table('services')->where('nom_service', $user->service)->value('id');

                DB::table('users')->where('id', $user->id)->update([
                    'direction_id' => $directionId,
                    'service_id' => $serviceId,
                ]);
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['direction', 'service']);
            });
        }
    }

    public function down(): void
    {
        // Rien à annuler : cette migration ne fait que vérifier/convertir
    }
};