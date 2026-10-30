<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permite que un capitán de compañía cree inspecciones:
 *  - Agrega `creado_por` (usuario que creó la inspección).
 *  - Permite `cuartelero_id` nulo (el capitán no es cuartelero).
 *  - Rellena `creado_por` en las inspecciones existentes con el usuario del cuartelero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_inspecciones', function (Blueprint $table) {
            $table->foreignId('creado_por')
                ->nullable()
                ->after('cuartelero_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        // cuartelero_id pasa a ser opcional.
        // Se usa SQL directo para no depender de doctrine/dbal en versiones antiguas de Laravel.
        DB::statement('ALTER TABLE checklist_inspecciones MODIFY cuartelero_id BIGINT UNSIGNED NULL');

        // Inspecciones existentes: el creador es el usuario asociado al cuartelero
        DB::statement('
            UPDATE checklist_inspecciones i
            JOIN users u ON u.cuartelero_id = i.cuartelero_id
            SET i.creado_por = u.id
            WHERE i.creado_por IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('checklist_inspecciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('creado_por');
        });

        // Ojo: falla si ya existen inspecciones sin cuartelero (creadas por capitanes)
        DB::statement('ALTER TABLE checklist_inspecciones MODIFY cuartelero_id BIGINT UNSIGNED NOT NULL');
    }
};