<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();

            // Sección a la que pertenece
            $table->foreignId('seccion_id')->constrained('checklist_secciones')->cascadeOnDelete();

            // Nombre del ítem que ve el cuartelero
            $table->string('nombre'); // "Aceite", "Sirena Q2B", "Luces Bajas", etc.

            // Si es crítico, un "Malo" genera alerta de severidad CRÍTICA
            $table->boolean('es_critico')->default(false);

            // Orden de aparición dentro de la sección
            $table->unsignedSmallInteger('orden')->default(0);

            // Permite desactivar ítems sin eliminarlos
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->index(['seccion_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};