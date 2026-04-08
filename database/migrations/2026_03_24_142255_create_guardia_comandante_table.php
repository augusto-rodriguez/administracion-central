<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_comandante', function (Blueprint $table) {
            $table->id();

            $table->foreignId('voluntario_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->date('fecha_inicio'); 
            $table->date('fecha_fin');    

            $table->timestamps();

            // Solo un comandante de guardia por semana
            $table->unique('fecha_inicio');

            $table->index('voluntario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_comandante');
    }
};