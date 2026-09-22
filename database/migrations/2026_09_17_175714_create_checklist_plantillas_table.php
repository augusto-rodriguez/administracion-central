<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_plantillas', function (Blueprint $table) {
            $table->id();

            // Nombre descriptivo de la plantilla
            $table->string('nombre'); // "Checklist Bomba", "Checklist Rescate", etc.

            // Descripción opcional con instrucciones generales
            $table->text('descripcion')->nullable();

            // Tipo de unidad al que aplica (null = aplica a todas)
            $table->string('tipo_unidad', 50)->nullable()->index(); // bomba, rescate, portaescala, etc.

            // Estado de la plantilla
            $table->boolean('activa')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_plantillas');
    }
};