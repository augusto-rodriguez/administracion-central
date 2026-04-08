<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_nocturna_voluntario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guardia_nocturna_compania_id')
                  ->constrained('guardia_nocturna_compania')
                  ->cascadeOnDelete();

            $table->foreignId('voluntario_id')
                  ->constrained('voluntarios')
                  ->cascadeOnDelete();

            $table->timestamps();

            // Un voluntario solo puede aparecer una vez por compañía por guardia
            $table->unique(['guardia_nocturna_compania_id', 'voluntario_id'], 'gn_voluntario_unique');

            $table->index('guardia_nocturna_compania_id');
            $table->index('voluntario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_nocturna_voluntario');
    }
};