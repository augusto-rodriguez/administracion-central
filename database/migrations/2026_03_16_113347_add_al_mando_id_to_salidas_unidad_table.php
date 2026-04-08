<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salidas_unidad', function (Blueprint $table) {
            $table->foreignId('al_mando_id')
                ->nullable()
                ->after('oficial_id') 
                ->constrained('voluntarios')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('salidas_unidad', function (Blueprint $table) {
            $table->dropForeign(['al_mando_id']);
            $table->dropColumn('al_mando_id');
        });
    }
};