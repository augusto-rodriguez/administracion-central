<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea el catálogo de cargos disponibles en el Cuerpo de Bomberos.
     * Un cargo puede ser de tipo "compania" (pertenece a una compañía específica)
     * o "general" (pertenece al Cuerpo completo).
     */
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();

            // Nombre descriptivo del cargo (ej: "Capitán", "Superintendente")
            $table->string('nombre');

            // Código o clave del cargo (ej: 103 para Capitán de cualquier compañía)
            // Único por tipo: no pueden existir dos "Capitán" en el catálogo general
            $table->string('codigo')->unique();

            // Tipo: 'compania' | 'general'
            // - compania: el cargo existe dentro de una compañía específica
            // - general:  el cargo pertenece al Cuerpo completo
            $table->enum('tipo', ['compania', 'general']);

            // Orden de jerarquía para mostrar en listados (menor = mayor jerarquía)
            $table->unsignedSmallInteger('orden')->default(99);

            // Descripción opcional del cargo
            $table->text('descripcion')->nullable();

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};