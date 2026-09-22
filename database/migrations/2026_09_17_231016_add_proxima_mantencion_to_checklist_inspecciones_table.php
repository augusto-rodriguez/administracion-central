<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_inspecciones', function (Blueprint $table) {
            $table->string('proxima_mantencion', 100)->nullable()->after('hora_ultimo_cambio_aceite');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_inspecciones', function (Blueprint $table) {
            $table->dropColumn('proxima_mantencion');
        });
    }
};
