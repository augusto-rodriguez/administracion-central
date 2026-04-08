<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_turno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->onDelete('cascade');
            $table->datetime('entrada_at');
            $table->datetime('salida_at')->nullable();
            $table->integer('total_minutos')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_turno');
    }
};
