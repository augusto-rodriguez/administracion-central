<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compania_especialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compania_id')
                ->constrained('companias')
                ->onDelete('cascade');
            $table->foreignId('especialidad_id')
                ->constrained('especialidades')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['compania_id', 'especialidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compania_especialidad');
    }
};