<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuartelero_unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuartelero_id')->constrained('cuarteleros')->onDelete('cascade');
            $table->foreignId('unidad_id')->constrained('unidades')->onDelete('cascade');
            $table->unique(['cuartelero_id', 'unidad_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuartelero_unidad');
    }
};
