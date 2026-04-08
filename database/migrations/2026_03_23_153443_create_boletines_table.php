<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletines', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->enum('tipo', ['am', 'pm']); // 10:00 o 21:00

            $table->timestamps();

            $table->unique(['fecha', 'tipo']); // 🔥 clave importante
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletines');
    }
};