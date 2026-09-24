<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE checklist_hallazgos MODIFY COLUMN estado ENUM('abierto', 'en_revision', 'en_reparacion', 'resuelto_verificado') NOT NULL DEFAULT 'abierto'");

        // Migrar datos existentes
        DB::table('checklist_hallazgos')->where('estado', 'resuelto')->update(['estado' => 'resuelto_verificado']);
        DB::table('checklist_hallazgos')->where('estado', 'verificado')->update(['estado' => 'resuelto_verificado']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE checklist_hallazgos MODIFY COLUMN estado ENUM('abierto', 'en_revision', 'en_reparacion', 'resuelto', 'verificado') NOT NULL DEFAULT 'abierto'");
    }
};
