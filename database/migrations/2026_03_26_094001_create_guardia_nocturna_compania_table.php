<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_nocturna_compania', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guardia_nocturna_id')
                  ->constrained('guardia_nocturna')
                  ->cascadeOnDelete();

            $table->foreignId('compania_id')
                  ->constrained('companias')
                  ->cascadeOnDelete();

            $table->foreignId('oficial_a_cargo_id')
                  ->nullable()
                  ->constrained('voluntarios')
                  ->nullOnDelete();

            $table->foreignId('cuartelero_id')
                  ->nullable()
                  ->constrained('cuarteleros')
                  ->nullOnDelete();

            $table->boolean('sin_reporte')->default(false);
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // Una compañía solo puede aparecer una vez por guardia
            $table->unique(['guardia_nocturna_id', 'compania_id']);

            $table->index('guardia_nocturna_id');
            $table->index('compania_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_nocturna_compania');
    }
};