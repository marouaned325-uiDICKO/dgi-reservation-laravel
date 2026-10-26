<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('salle_id')->constrained()->onDelete('cascade');
            $table->string('motif');
            $table->enum('statut', ['en_attente', 'confirmee', 'annulee', 'refusee'])->default('en_attente');
            $table->string('email');
            $table->date('date_reserv');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->unsignedInteger('nombre');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};