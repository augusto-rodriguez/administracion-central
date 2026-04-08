<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salidas_unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->onDelete('cascade');
            $table->foreignId('clave_salida_id')->constrained('claves_salida')->onDelete('cascade');
            $table->foreignId('voluntario_id')->nullable()->constrained('voluntarios')->onDelete('set null');
            $table->foreignId('oficial_id')->nullable()->constrained('voluntarios')->onDelete('set null');
            $table->string('conductor_libre')->nullable();
            $table->string('direccion');
            $table->integer('cantidad_personal')->nullable();
            $table->decimal('km_salida', 10, 1)->nullable();
            $table->decimal('km_llegada', 10, 1)->nullable();
            $table->decimal('km_recorrido', 10, 1)->nullable();
            $table->datetime('salida_at');
            $table->datetime('llegada_at')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salidas_unidad');
    }
};
