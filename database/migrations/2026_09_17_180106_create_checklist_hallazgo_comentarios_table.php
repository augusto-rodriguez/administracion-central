<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_hallazgo_comentarios', function (Blueprint $table) {
            $table->id();

            // Hallazgo al que pertenece el comentario
            $table->foreignId('hallazgo_id')->constrained('checklist_hallazgos')->cascadeOnDelete();

            // Usuario que escribió el comentario
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // Texto del comentario
            $table->text('comentario');

            // Si el comentario acompaña un cambio de estado, se registra el antes/después
            // Esto permite reconstruir la línea de tiempo completa del hallazgo
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['hallazgo_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_hallazgo_comentarios');
    }
};