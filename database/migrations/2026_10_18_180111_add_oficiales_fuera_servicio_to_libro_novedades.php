<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libro_novedades', function (Blueprint $table) {
            $table->json('oficiales_fuera_servicio_al_recibir')->nullable()
                ->after('unidades_fuera_servicio_al_recibir');
            $table->json('oficiales_fuera_servicio_al_entregar')->nullable()
                ->after('unidades_fuera_servicio_al_entregar');
        });
    }

    public function down(): void
    {
        Schema::table('libro_novedades', function (Blueprint $table) {
            $table->dropColumn(['oficiales_fuera_servicio_al_recibir', 'oficiales_fuera_servicio_al_entregar']);
        });
    }
};
