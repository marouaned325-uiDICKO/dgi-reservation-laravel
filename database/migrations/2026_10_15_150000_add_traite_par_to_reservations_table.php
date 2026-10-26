<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'traite_par')) {
                $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('reservations', 'traite_le')) {
                $table->timestamp('traite_le')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'traite_par')) {
                $table->dropConstrainedForeignId('traite_par');
            }
            if (Schema::hasColumn('reservations', 'traite_le')) {
                $table->dropColumn('traite_le');
            }
        });
    }
};