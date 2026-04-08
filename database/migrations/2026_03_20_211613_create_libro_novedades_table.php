<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libro_novedades', function (Blueprint $table) {
            $table->id();

            // ── IDENTIFICACIÓN DEL TURNO ─────────────────────────────────────
            $table->date('fecha');
            $table->enum('turno', ['dia', 'noche'])
                  ->comment('dia = 08-20 hrs  |  noche = 20-08 hrs');

            // Horario real del turno (puede variar del estándar 08/20)
            $table->time('hora_inicio')->default('08:00:00');
            $table->time('hora_fin')->default('20:00:00');

            // ── OPERADORES ───────────────────────────────────────────────────
            // Centralista que recibe y lleva el turno
            $table->foreignId('operador_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->comment('Usuario con rol operador/centralista que cubre el turno');

            // Nombre libre del operador saliente (puede no estar en el sistema)
            $table->string('operador_turno_anterior')
                  ->nullable()
                  ->comment('Nombre del centralista del turno anterior al recibir');

            // ── UNIDADES AL RECIBIR EL TURNO ─────────────────────────────────
            // Maquinistas con unidad asignada al inicio del turno (snapshot JSON)
            $table->json('maquinistas_al_recibir')
                  ->nullable()
                  ->comment('Snapshot de registros_turno activos al inicio: [{voluntario_id, nombre, unidad_id, unidad_nombre}]');

            // Cuarteleros activos al inicio del turno
            $table->json('cuarteleros_al_recibir')
                  ->nullable()
                  ->comment('Snapshot de registros_turno_cuartelero activos al inicio: [{cuartelero_id, nombre, unidad_id}]');

            // Unidades inactivas (malas) al RECIBIR el turno
            $table->json('unidades_fuera_servicio_al_recibir')
                  ->nullable()
                  ->comment('unidades con activa=false al inicio del turno: [{unidad_id, nombre, patente}]');

            // ── UNIDADES AL ENTREGAR EL TURNO ────────────────────────────────
            // Maquinistas con unidad asignada al cierre del turno
            $table->json('maquinistas_al_entregar')
                  ->nullable()
                  ->comment('Snapshot de registros_turno activos al cierre: [{voluntario_id, nombre, unidad_id, unidad_nombre}]');

            // Cuarteleros activos al cierre del turno
            $table->json('cuarteleros_al_entregar')
                  ->nullable()
                  ->comment('Snapshot de registros_turno_cuartelero activos al cierre');

            // Unidades inactivas (malas) al ENTREGAR el turno
            $table->json('unidades_fuera_servicio_al_entregar')
                  ->nullable()
                  ->comment('unidades con activa=false al cierre del turno: [{unidad_id, nombre, patente}]');

            // ── MOVIMIENTOS DURANTE EL TURNO ─────────────────────────────────
            // Puestas en servicio de unidades ocurridas dentro del turno
            // (filtra salidas_unidad del turno donde llegada_at IS NOT NULL y km_llegada registrado)
            $table->json('puestas_en_servicio')
                  ->nullable()
                  ->comment('IDs de salidas_unidad completadas en el turno: [salida_unidad_id, ...]');

            // Salidas administrativas del turno (clave_salida.tipo = administrativa)
            $table->json('salidas_administrativas')
                  ->nullable()
                  ->comment('IDs de salidas_unidad administrativas del turno');

            // Salidas de emergencia del turno (clave_salida.tipo = emergencia)
            $table->json('salidas_emergencia')
                  ->nullable()
                  ->comment('IDs de salidas_unidad de emergencia del turno');

            // ── NOTAS EXCLUSIVAS DEL TURNO (no existen en otras tablas) ──────
            $table->text('novedades_cronologicas')
                  ->nullable()
                  ->comment('Texto libre: cronología de novedades ocurridas en el turno');

            $table->text('observaciones_telecomunicaciones')
                  ->nullable()
                  ->comment('Texto libre: observaciones en telecomunicaciones');

            $table->text('novedades_viper')
                  ->nullable()
                  ->comment('Texto libre: novedades del VIPER');

            // ── ESTADO DEL REGISTRO ──────────────────────────────────────────
            $table->enum('estado', ['borrador', 'cerrado'])
                  ->default('borrador')
                  ->comment('borrador = turno en curso  |  cerrado = turno finalizado y exportable');

            // Usuario que cerró/exportó el libro
            $table->foreignId('cerrado_por')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamp('cerrado_at')->nullable();

            $table->timestamps();

            // ── ÍNDICES ──────────────────────────────────────────────────────
            // Un solo libro por fecha + turno (no puede haber dos "noche" el mismo día)
            $table->unique(['fecha', 'turno']);
            $table->index(['fecha']);
            $table->index(['operador_id']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libro_novedades');
    }
};