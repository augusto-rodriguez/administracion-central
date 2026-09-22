<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_hallazgos', function (Blueprint $table) {
            $table->id();

            // Inspección que originó este hallazgo
            $table->foreignId('inspeccion_id')->constrained('checklist_inspecciones')->cascadeOnDelete();

            // Ítem específico donde se detectó el problema
            $table->foreignId('item_id')->constrained('checklist_items')->restrictOnDelete();

            // Severidad del hallazgo (se calcula automáticamente al enviar el checklist)
            // critico: ítem marcado "Malo" en sección crítica (seguridad, frenos, etc.)
            // atencion: ítem "Regular" o nivel bajo 1/2
            // info: documento próximo a vencer u observación menor
            $table->enum('severidad', ['critico', 'atencion', 'info'])->index();

            // Descripción adicional del problema (la llena el cuartelero o el oficial)
            $table->text('descripcion')->nullable();

            // Ciclo de vida del hallazgo
            $table->enum('estado', [
                'abierto',        // Recién detectado
                'en_revision',    // Un oficial lo está evaluando
                'en_reparacion',  // Se asignó trabajo de reparación
                'resuelto',       // Se reparó, pendiente de verificación
                'verificado',     // Un oficial verificó que está OK
            ])->default('abierto')->index();

            // Oficial o responsable asignado para resolver
            $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();

            // Datos de resolución
            $table->timestamp('resuelto_at')->nullable();
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->nullOnDelete();

            // Control de notificaciones enviadas
            $table->boolean('notificado')->default(false);
            $table->timestamp('notificado_at')->nullable();

            $table->timestamps();

            // Índices para consultas frecuentes del dashboard
            $table->index(['estado', 'severidad']);
            $table->index(['asignado_a', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_hallazgos');
    }
};