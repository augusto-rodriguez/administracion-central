<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite que una misma unidad aparezca más de una vez en la guardia nocturna
 * de una compañía (conductor principal + conductor secundario).
 *
 * Antes: único (compañía, unidad)
 * Ahora: único (compañía, unidad, maquinista, cuartelero) → sigue impidiendo
 *        repetir exactamente el mismo conductor en la misma unidad.
 *
 * Primero se crea el índice nuevo y luego se borra el antiguo, para que la
 * foreign key de guardia_nocturna_compania_id nunca quede sin índice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardia_nocturna_unidad', function (Blueprint $table) {
            $table->unique(
                ['guardia_nocturna_compania_id', 'unidad_id', 'maquinista_id', 'cuartelero_id'],
                'gn_unidad_conductor_unique'
            );
        });

        Schema::table('guardia_nocturna_unidad', function (Blueprint $table) {
            $table->dropUnique('gn_unidad_unique');
        });
    }

    public function down(): void
    {
        // Ojo: fallará si ya existen guardias con conductor secundario guardado
        Schema::table('guardia_nocturna_unidad', function (Blueprint $table) {
            $table->unique(['guardia_nocturna_compania_id', 'unidad_id'], 'gn_unidad_unique');
        });

        Schema::table('guardia_nocturna_unidad', function (Blueprint $table) {
            $table->dropUnique('gn_unidad_conductor_unique');
        });
    }
};