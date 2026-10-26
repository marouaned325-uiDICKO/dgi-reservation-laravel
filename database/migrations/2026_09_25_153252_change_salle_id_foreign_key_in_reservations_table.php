<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['salle_id']);
            $table->foreignId('salle_id')->nullable()->change();
            $table->foreign('salle_id')->references('id')->on('salles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['salle_id']);
            $table->foreignId('salle_id')->nullable(false)->change();
            $table->foreign('salle_id')->references('id')->on('salles')->cascadeOnDelete();
        });
    }
};