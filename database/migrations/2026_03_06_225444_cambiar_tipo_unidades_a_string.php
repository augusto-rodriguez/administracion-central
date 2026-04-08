<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->string('tipo')->change();
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->enum('tipo', ['Autobomba', 'Escala', 'Rescate', 'Química', 'Otro'])->change();
        });
    }
};
