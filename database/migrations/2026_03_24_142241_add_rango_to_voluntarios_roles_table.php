<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voluntario_roles', function (Blueprint $table) {  
            $table->string('rango')->nullable()->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('voluntario_roles', function (Blueprint $table) { 
            $table->dropColumn('rango');
        });
    }
};