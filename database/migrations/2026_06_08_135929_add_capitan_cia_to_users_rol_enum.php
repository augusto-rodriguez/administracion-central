<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
 
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'comandante', 'capitan_cia', 'operador') NOT NULL DEFAULT 'operador'");
    }
 
    public function down(): void
    {
        // Primero convertir los capitan_cia a operador para evitar error al revertir
        DB::statement("UPDATE users SET rol = 'operador' WHERE rol = 'capitan_cia'");
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'comandante', 'operador') NOT NULL DEFAULT 'operador'");
    }
};
 