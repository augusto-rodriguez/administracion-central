<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_secciones', function (Blueprint $table) {
            $table->id();

            // Plantilla a la que pertenece
            $table->foreignId('plantilla_id')->constrained('checklist_plantillas')->cascadeOnDelete();

            // Nombre de la sección
            $table->string('nombre'); // "Revisión de Niveles", "Luces", "Interior Cabina", etc.

            // Instrucciones para el cuartelero al completar esta sección
            $table->text('descripcion')->nullable();

            // Tipo de respuesta que usan los ítems de esta sección
            // nivel: 1/4, 1/2, 3/4, FULL, NO APLICA
            // estado: Bueno, Regular, Malo, NO APLICA
            // documento: OK, Vencido
            $table->enum('tipo_respuesta', ['nivel', 'estado', 'documento']);

            // Orden de aparición en el formulario
            $table->unsignedSmallInteger('orden')->default(0);

            // Permite desactivar secciones sin eliminarlas
            $table->boolean('activa')->default(true);

            $table->timestamps();

            $table->index(['plantilla_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_secciones');
    }
};