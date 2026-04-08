<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_nocturna', function (Blueprint $table) {
            $table->id();

            $table->date('fecha')->unique();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');

            $table->foreignId('cerrado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('cerrado_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_nocturna');
    }
};