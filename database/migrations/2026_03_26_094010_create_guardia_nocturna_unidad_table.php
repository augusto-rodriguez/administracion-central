<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_nocturna_unidad', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guardia_nocturna_compania_id')
                  ->constrained('guardia_nocturna_compania')
                  ->cascadeOnDelete();

            $table->foreignId('unidad_id')
                  ->constrained('unidades')
                  ->cascadeOnDelete();

            // Responsable de la unidad: maquinista O cuartelero, nunca ambos
            $table->foreignId('maquinista_id')
                  ->nullable()
                  ->constrained('voluntarios')
                  ->nullOnDelete();

            $table->foreignId('cuartelero_id')
                  ->nullable()
                  ->constrained('cuarteleros')
                  ->nullOnDelete();

            $table->timestamps();

            // Una unidad solo puede aparecer una vez por compañía por guardia
            $table->unique(['guardia_nocturna_compania_id', 'unidad_id'], 'gn_unidad_unique');

            $table->index('guardia_nocturna_compania_id');
            $table->index('unidad_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_nocturna_unidad');
    }
};