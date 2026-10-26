<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direction_id')->constrained('directions')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->timestamps();

            $table->unique(['direction_id', 'nom']);
        });

        $now = now();
        $parDefaut = ['Vidéoprojecteur', 'Écran', 'Climatisation', 'Tableau blanc', 'Micro et sonorisation', 'Wi-Fi', 'Visioconférence', 'Ordinateur'];

        // Chaque direction existante reçoit la liste habituelle d'équipements
        foreach (DB::table('directions')->pluck('id') as $directionId) {
            foreach ($parDefaut as $nom) {
                DB::table('equipements')->insertOrIgnore([
                    'direction_id' => $directionId,
                    'nom' => $nom,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Et on reprend aussi les équipements déjà saisis sur les salles existantes
        foreach (DB::table('salles')->whereNotNull('direction_id')->whereNotNull('equipement')->get(['direction_id', 'equipement']) as $salle) {
            foreach (preg_split('/[,;\n]+/', (string) $salle->equipement) as $nom) {
                $nom = trim($nom);

                if ($nom === '' || mb_strlen($nom) > 100) {
                    continue;
                }

                DB::table('equipements')->insertOrIgnore([
                    'direction_id' => $salle->direction_id,
                    'nom' => $nom,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};