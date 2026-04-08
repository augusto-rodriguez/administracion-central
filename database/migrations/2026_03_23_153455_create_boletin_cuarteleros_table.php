<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boletin_cuarteleros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('boletin_id')
                  ->constrained('boletines')
                  ->cascadeOnDelete();

            $table->foreignId('cuartelero_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->timestamps();

            // Evita duplicados
            $table->unique(['boletin_id', 'cuartelero_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletin_cuarteleros');
    }
};