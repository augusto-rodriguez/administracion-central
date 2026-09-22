<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_inspecciones', function (Blueprint $table) {
            $table->id();

            // Unidad inspeccionada
            $table->foreignId('unidad_id')->constrained('unidades')->restrictOnDelete();

            // Cuartelero que realiza la inspección
            $table->foreignId('cuartelero_id')->constrained('cuarteleros')->restrictOnDelete();

            // Plantilla utilizada (se guarda para trazabilidad aunque la plantilla cambie después)
            $table->foreignId('plantilla_id')->constrained('checklist_plantillas')->restrictOnDelete();

            // Fecha de la inspección
            $table->date('fecha')->index();

            // Datos operativos de la unidad al momento de la inspección
            $table->string('kilometraje', 50)->nullable();
            $table->string('hora_motor', 50)->nullable();
            $table->string('hora_bomba', 50)->nullable();
            $table->string('hora_ultimo_cambio_aceite', 100)->nullable();

            // Observaciones generales del cuartelero
            $table->text('observaciones')->nullable();

            // Estado del checklist: borrador permite autoguardado, completado es el envío final
            $table->enum('estado', ['borrador', 'completado'])->default('borrador')->index();

            // Momento exacto en que se marcó como completado
            $table->timestamp('completado_at')->nullable();

            $table->timestamps();

            // Índices compuestos para consultas frecuentes
            $table->index(['unidad_id', 'fecha']);
            $table->index(['cuartelero_id', 'fecha']);
            $table->index(['estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_inspecciones');
    }
};