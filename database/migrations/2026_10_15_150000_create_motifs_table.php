<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direction_id')->constrained('directions')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->timestamps();

            $table->unique(['direction_id', 'nom']);
        });

        $now = now();
        $parDefaut = ['CONFÉRENCE', 'RÉUNION', 'FORMATION', 'ATÉLIER', 'CONTRÔLE', 'AUDIT', 'INTERVENTION', 'SÉMINAIRE'];

        // Chaque direction existante reçoit la liste habituelle de motifs
        foreach (DB::table('directions')->pluck('id') as $directionId) {
            foreach ($parDefaut as $nom) {
                DB::table('motifs')->insertOrIgnore([
                    'direction_id' => $directionId,
                    'nom' => $nom,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('motifs');
    }
};