<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->foreignId('checklist_plantilla_id')
                ->nullable()
                ->after('activa')
                ->constrained('checklist_plantillas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropForeign(['checklist_plantilla_id']);
            $table->dropColumn('checklist_plantilla_id');
        });
    }
};
