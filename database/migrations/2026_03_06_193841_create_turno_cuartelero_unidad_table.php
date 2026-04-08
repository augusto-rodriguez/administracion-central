<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno_cuartelero_unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('registros_turno_cuartelero')->onDelete('cascade');
            $table->foreignId('unidad_id')->constrained('unidades')->onDelete('cascade');
            $table->unique(['turno_id', 'unidad_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno_cuartelero_unidad');
    }
};
