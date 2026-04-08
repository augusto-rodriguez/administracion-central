<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers_combustible', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_carga');
            $table->foreignId('unidad_id')->constrained('unidades');
            $table->unsignedInteger('km_carga');
            $table->string('conductor_nombre'); // nombre libre guardado
            $table->string('numero_voucher')->unique();
            $table->decimal('litros', 8, 3);
            $table->unsignedInteger('valor_unitario'); // pesos CLP
            $table->unsignedInteger('total');          // calculado litros * valor_unitario
            $table->string('observaciones')->nullable();
            $table->foreignId('registrado_por')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers_combustible');
    }
};