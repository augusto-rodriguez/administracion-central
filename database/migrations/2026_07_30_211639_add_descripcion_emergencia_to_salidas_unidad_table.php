<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salidas_unidad', function (Blueprint $table) {
            $table->string('descripcion_emergencia', 500)->nullable()->after('observaciones');
        });
    }

    public function down()
    {
        Schema::table('salidas_unidad', function (Blueprint $table) {
            $table->dropColumn('descripcion_emergencia');
        });
    }
};