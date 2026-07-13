<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();

            // Usuario (nullable para intentos fallidos donde no se encuentra el user)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Evento: login, logout, failed, lockout
            $table->enum('evento', ['login', 'logout', 'failed', 'lockout'])->index();

            // Email ingresado (útil para intentos fallidos)
            $table->string('email')->nullable();

            // Datos de conexión
            $table->ipAddress('ip');
            $table->string('user_agent', 512)->nullable();

            // Geolocalización aproximada (se puede poblar con un servicio externo)
            $table->string('ciudad')->nullable();
            $table->string('pais')->nullable();

            // Dispositivo / navegador parseado
            $table->string('navegador')->nullable();
            $table->string('plataforma')->nullable(); // Windows, macOS, Android, etc.
            $table->string('dispositivo')->nullable(); // desktop, mobile, tablet

            // Sesión
            $table->string('session_id')->nullable()->index();

            // Resultado y motivo (para failed: 'credenciales', 'cuenta_inactiva', etc.)
            $table->boolean('exitoso')->default(false);
            $table->string('motivo_fallo')->nullable();

            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};