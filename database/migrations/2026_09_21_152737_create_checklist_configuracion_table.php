<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('valor');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Insertar configuración inicial
        DB::table('checklist_configuracion')->insert([
            [
                'clave'       => 'hora_limite_inspeccion',
                'valor'       => '23:59',
                'descripcion' => 'Hora límite para completar una inspección iniciada en el día (formato HH:MM)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'max_inspecciones_unidad_dia',
                'valor'       => '1',
                'descripcion' => 'Cantidad máxima de inspecciones completadas por unidad por día',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_configuracion');
    }
};
