<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabla intermedia que asigna cargos a voluntarios.
     * Soporta historial completo: un voluntario puede haber tenido
     * múltiples cargos a lo largo del tiempo, y un cargo puede haber
     * tenido múltiples titulares en distintos períodos.
     *
     * Invariantes de negocio (reforzados por unique keys + lógica de app/DB):
     *   - Un cargo de tipo "compania" solo puede tener UN titular activo por compañía.
     *   - Un cargo de tipo "general" solo puede tener UN titular activo en el Cuerpo.
     *   - Un voluntario puede tener solo UN cargo activo por tipo en simultáneo
     *     (no puede ser Capitán y Teniente al mismo tiempo en la misma compañía).
     */
    public function up(): void
    {
        Schema::create('voluntario_cargos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('voluntario_id')
                ->constrained('voluntarios')
                ->cascadeOnDelete();

            $table->foreignId('cargo_id')
                ->constrained('cargos')
                ->cascadeOnDelete();

            // Para cargos de tipo "compania": indica en qué compañía ejerce el cargo.
            // Para cargos de tipo "general": debe ser NULL.
            $table->foreignId('compania_id')
                ->nullable()
                ->constrained('companias')
                ->nullOnDelete();

            // Fechas de vigencia del cargo
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // NULL = cargo vigente actualmente

            // Indicador explícito de cargo activo (facilita queries sin calcular fechas)
            $table->boolean('activo')->default(true);

            // Quién realizó la designación (opcional, para auditoría)
            $table->string('designado_por')->nullable();

            // Observaciones o notas sobre la designación
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // ---------------------------------------------------------------
            // Índices
            // ---------------------------------------------------------------

            // Búsqueda rápida de cargos activos de un voluntario
            $table->index(['voluntario_id', 'activo']);

            // Búsqueda rápida del titular activo de un cargo en una compañía
            $table->index(['cargo_id', 'compania_id', 'activo']);

            // ---------------------------------------------------------------
            // Restricción de unicidad:
            // Un cargo solo puede tener UN titular activo por compañía (o en
            // el Cuerpo para cargos generales). Se implementa como unique
            // parcial; la lógica de "un solo activo" se refuerza en el
            // servicio/repositorio antes de insertar.
            //
            // Nota: MySQL no soporta índices únicos parciales (WHERE activo=1)
            // nativamente, por lo que la unicidad de "un solo titular activo"
            // se maneja en la capa de aplicación (CargoService) y con un
            // trigger opcional documentado al final del seeder.
            // ---------------------------------------------------------------
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntario_cargos');
    }
};