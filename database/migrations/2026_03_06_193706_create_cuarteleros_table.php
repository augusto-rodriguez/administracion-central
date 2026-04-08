<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuarteleros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compania_id')->constrained('companias')->onDelete('cascade');
            $table->string('nombre');
            $table->string('rut')->unique()->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuarteleros');
    }
};
