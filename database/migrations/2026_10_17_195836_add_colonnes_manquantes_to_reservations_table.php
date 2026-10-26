<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'direction')) {
                $table->string('direction')->nullable();
            }
            if (!Schema::hasColumn('reservations', 'service')) {
                $table->string('service')->nullable();
            }
            if (!Schema::hasColumn('reservations', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('reservations', 'date_fin')) {
                $table->date('date_fin')->nullable();
            }
            if (!Schema::hasColumn('reservations', 'traite_par')) {
                $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            foreach (['direction', 'service', 'email'] as $col) {
                if (Schema::hasColumn('reservations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};