<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->string('clave_actual', 5)->nullable()->after('rut');
        });
    }

    public function down(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropColumn('clave_actual');
        });
    }
};