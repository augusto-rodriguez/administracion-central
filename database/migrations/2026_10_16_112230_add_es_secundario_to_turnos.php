<?php

use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tablas(): array
    {
        return [(new RegistroTurno)->getTable(), (new RegistroTurnoCuartelero)->getTable()];
    }

    public function up(): void
    {
        foreach ($this->tablas() as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->boolean('es_secundario')->default(false)->after('observaciones');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablas() as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('es_secundario');
            });
        }
    }
};