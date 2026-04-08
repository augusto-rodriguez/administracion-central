<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletin_maquinistas', function (Blueprint $table) {
            $table->string('unidades_texto')->nullable()->after('unidad_id');
        });
    }

    public function down(): void
    {
        Schema::table('boletin_maquinistas', function (Blueprint $table) {
            $table->dropColumn('unidades_texto');
        });
    }
};