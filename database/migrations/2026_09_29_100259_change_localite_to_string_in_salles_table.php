<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salles', function (Blueprint $table) {
            $table->string('localite')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('salles', function (Blueprint $table) {
            $table->enum('localite', [
                'Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Kaya', 'Ouahigouya',
                'Banfora', "Fada N'Gourma", 'Tenkodogo', 'Dédougou', 'Dori',
                'Gaoua', 'Manga', 'Ziniaré',
            ])->nullable()->change();
        });
    }
};