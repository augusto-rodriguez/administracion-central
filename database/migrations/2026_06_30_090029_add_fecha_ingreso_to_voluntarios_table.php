<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->date('fecha_ingreso')->nullable()->after('activo');
        });
    }

    public function down()
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropColumn('fecha_ingreso');
        });
    }
};
