<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compania_id')->constrained('companias')->onDelete('cascade');
            $table->string('nombre');
            $table->string('rut')->unique()->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('voluntario_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->onDelete('cascade');
            $table->string('rol');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['voluntario_id', 'rol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntario_roles');
        Schema::dropIfExists('voluntarios');
    }
};
