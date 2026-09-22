<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'comandante', 'capitan_cia', 'operador', 'cuartelero') NOT NULL DEFAULT 'operador'");

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('cuartelero_id')
                ->nullable()
                ->after('rol')
                ->constrained('cuarteleros')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cuartelero_id']);
            $table->dropColumn('cuartelero_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'comandante', 'capitan_cia', 'operador') NOT NULL DEFAULT 'operador'");
    }
};