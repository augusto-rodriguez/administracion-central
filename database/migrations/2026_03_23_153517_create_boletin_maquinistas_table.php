<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boletin_maquinistas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('boletin_id')
                  ->constrained('boletines')
                  ->cascadeOnDelete();

            $table->foreignId('voluntario_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('unidad_id')
            ->constrained('unidades')
            ->cascadeOnDelete();

            // Estado del maquinista (ej: 6-20 = en servicio)
            $table->string('estado', 10)->default('6-20');

            $table->timestamps();

            // Un voluntario solo una vez por boletín
            $table->unique(['boletin_id', 'voluntario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletin_maquinistas');
    }
};