<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardia_nocturna_voluntario', function (Blueprint $table) {
            $table->time('hora_salida')->nullable()->after('hora_ingreso');
            // null = salió después del cierre (se asume 07:00)
        });
    }

    public function down(): void
    {
        Schema::table('guardia_nocturna_voluntario', function (Blueprint $table) {
            $table->dropColumn('hora_salida');
        });
    }
};