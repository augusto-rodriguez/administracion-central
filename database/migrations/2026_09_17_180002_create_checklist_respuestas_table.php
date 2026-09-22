<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_respuestas', function (Blueprint $table) {
            $table->id();

            // Inspección a la que pertenece esta respuesta
            $table->foreignId('inspeccion_id')->constrained('checklist_inspecciones')->cascadeOnDelete();

            // Ítem que se está respondiendo
            $table->foreignId('item_id')->constrained('checklist_items')->restrictOnDelete();

            // Valor de la respuesta según el tipo de sección:
            // tipo "nivel":     '1/4', '1/2', '3/4', 'full', 'no_aplica'
            // tipo "estado":    'bueno', 'regular', 'malo', 'no_aplica'
            // tipo "documento": 'ok', 'vencido'
            $table->string('valor', 50);

            $table->timestamp('created_at')->useCurrent();

            // Evitar respuestas duplicadas para el mismo ítem en la misma inspección
            $table->unique(['inspeccion_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_respuestas');
    }
};