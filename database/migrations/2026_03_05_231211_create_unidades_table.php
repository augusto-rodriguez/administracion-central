<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compania_id')->constrained('companias')->onDelete('cascade');
            $table->string('nombre');
            $table->string('patente')->unique()->nullable();
            $table->enum('tipo', ['Autobomba', 'Escala', 'Rescate', 'Química', 'Otro']);
            $table->string('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
