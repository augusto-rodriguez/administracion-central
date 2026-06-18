<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardia_nocturna_compania', function (Blueprint $table) {
            $table->unsignedTinyInteger('operadores_rescate')->nullable()->after('observaciones');
            $table->unsignedTinyInteger('operadores_hazmat')->nullable()->after('operadores_rescate');
            $table->unsignedTinyInteger('tecnicos_hazmat')->nullable()->after('operadores_hazmat');
        });
    }

    public function down(): void
    {
        Schema::table('guardia_nocturna_compania', function (Blueprint $table) {
            $table->dropColumn(['operadores_rescate', 'operadores_hazmat', 'tecnicos_hazmat']);
        });
    }
};