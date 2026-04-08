<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citaciones', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('compania_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medio_recepcion_id')
                  ->constrained('medios_recepcion_citaciones')
                  ->cascadeOnDelete();

            // Contenido
            $table->text('mensaje'); // texto digitado

            // Opcional pero recomendado
            $table->dateTime('fecha_citacion')->nullable();

            $table->timestamps();

            // Índices (siguiendo tu estilo)
            $table->index('compania_id');
            $table->index('medio_recepcion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citaciones');
    }
};