<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_hallazgo_fotos', function (Blueprint $table) {
            $table->id();

            // Hallazgo al que pertenece la foto
            $table->foreignId('hallazgo_id')->constrained('checklist_hallazgos')->cascadeOnDelete();

            // Ruta del archivo en storage (ej: checklist/hallazgos/2026/09/abc123.jpg)
            $table->string('ruta', 500);

            // Nombre original del archivo subido
            $table->string('nombre_original')->nullable();

            // Tipo de foto: problema (al detectar) o resolución (al reparar)
            $table->enum('tipo', ['problema', 'resolucion'])->default('problema');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_hallazgo_fotos');
    }
};