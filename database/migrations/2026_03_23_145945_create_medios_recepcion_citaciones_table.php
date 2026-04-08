<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medios_recepcion_citaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // WhatsApp, Correo, Teléfono, etc.
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medios_recepcion_citaciones');
    }
};