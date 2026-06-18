<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficiales_fuera_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voluntario_id')
                ->constrained('voluntarios')
                ->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // nullable = aún fuera de servicio
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficiales_fuera_servicio');
    }
};