<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salidas_unidad', function (Blueprint $table) {
            $table->foreignId('salida_padre_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('salidas_unidad')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salidas_unidad', function (Blueprint $table) {
            $table->dropForeign(['salida_padre_id']);
            $table->dropColumn('salida_padre_id');
        });
    }
};