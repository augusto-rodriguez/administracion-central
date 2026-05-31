<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Puebla el catálogo inicial de cargos.
 *
 * Los códigos siguen la convención:
 *   - Cargos de compañía: 3 dígitos (1XX)
 *   - Cargos generales del Cuerpo: 4 dígitos (9XXX)
 *
 * El campo "orden" define la jerarquía de visualización (1 = máxima jerarquía).
 */
class CargosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ------------------------------------------------------------------
        // Cargos de Compañía
        // Existirán N instancias de cada uno (una por compañía), distinguidas
        // en voluntario_cargos por el campo compania_id.
        // ------------------------------------------------------------------
        $cargosCompania = [
            ['codigo' => '41', 'nombre' => 'Capitán',         'orden' => 1],
            ['codigo' => '101', 'nombre' => 'Teniente 1°',     'orden' => 2],
            ['codigo' => '102', 'nombre' => 'Teniente 2°',     'orden' => 3],
            ['codigo' => '103', 'nombre' => 'Teniente 3°',     'orden' => 4],
            ['codigo' => '104', 'nombre' => 'Teniente 4°',     'orden' => 5],
            ['codigo' => '105', 'nombre' => 'Ayudante',      'orden' => 6],
            ['codigo' => '106', 'nombre' => 'Secretario',        'orden' => 7],
            ['codigo' => '107', 'nombre' => 'Tesorero',        'orden' => 8],
            ['codigo' => '71', 'nombre' => 'Director',        'orden' => 9],
        ];

        foreach ($cargosCompania as $cargo) {
            DB::table('cargos')->insertOrIgnore([
                'nombre'      => $cargo['nombre'],
                'codigo'      => $cargo['codigo'],
                'tipo'        => 'compania',
                'orden'       => $cargo['orden'],
                'descripcion' => null,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // ------------------------------------------------------------------
        // Cargos Generales del Cuerpo
        // Solo puede haber un titular activo a la vez en todo el Cuerpo.
        // ------------------------------------------------------------------
        $cargosGenerales = [
            ['codigo' => '6', 'nombre' => 'Superintendente',       'orden' => 1],
            ['codigo' => '7', 'nombre' => 'Vice Superintendente',  'orden' => 2],
            ['codigo' => '1', 'nombre' => 'Comandante',            'orden' => 3],
            ['codigo' => '2', 'nombre' => 'Segundo Comandante',    'orden' => 4],
            ['codigo' => '3', 'nombre' => 'Tercer Comandante',    'orden' => 5],
            ['codigo' => '9', 'nombre' => 'Secretario General',    'orden' => 6],
            ['codigo' => '8', 'nombre' => 'Tesorero General',      'orden' => 7],
        ];

        foreach ($cargosGenerales as $cargo) {
            DB::table('cargos')->insertOrIgnore([
                'nombre'      => $cargo['nombre'],
                'codigo'      => $cargo['codigo'],
                'tipo'        => 'general',
                'orden'       => $cargo['orden'],
                'descripcion' => null,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}