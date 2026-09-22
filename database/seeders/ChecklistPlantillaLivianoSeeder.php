<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChecklistPlantillaLivianoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $plantillaId = DB::table('checklist_plantillas')->insertGetId([
            'nombre'       => 'Chequeo Semanal de Material Liviano',
            'descripcion'  => 'Revisión visual-mecánica de camionetas y vehículos livianos del Cuerpo de Bomberos.',
            'tipo_unidad'  => 'liviano',
            'activa'       => true,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // ─── SECCIÓN 1: Revisión de Niveles ────────────────
        $seccionId = DB::table('checklist_secciones')->insertGetId([
            'plantilla_id'   => $plantillaId,
            'nombre'         => 'Revisión de Niveles',
            'descripcion'    => 'Para el nivel de aceite considerar FULL cuando se encuentre entre las marcas permitidas. Otros niveles rellenar si es necesario antes de marcar, dejar registrada cualquier acción en la caja de observaciones.',
            'tipo_respuesta' => 'nivel',
            'orden'          => 1,
            'activa'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->insertItems($seccionId, [
            ['nombre' => 'Aceite',                'es_critico' => true],
            ['nombre' => 'Refrigerante',          'es_critico' => true],
            ['nombre' => 'Combustible',            'es_critico' => true],
            ['nombre' => 'Ad-Blue',                'es_critico' => false],
            ['nombre' => 'Agua limpiaparabrisas',  'es_critico' => false],
        ], $now);

        // ─── SECCIÓN 2: Elementos de seguridad y alarma ────
        $seccionId = DB::table('checklist_secciones')->insertGetId([
            'plantilla_id'   => $plantillaId,
            'nombre'         => 'Elementos de seguridad y alarma',
            'descripcion'    => 'Probar el funcionamiento de los equipos antes de marcar.',
            'tipo_respuesta' => 'estado',
            'orden'          => 2,
            'activa'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->insertItems($seccionId, [
            ['nombre' => 'Balizas y destellantes',  'es_critico' => true],
            ['nombre' => 'Sirena electrónica',       'es_critico' => true],
            ['nombre' => 'Radio de comunicaciones',  'es_critico' => true],
            ['nombre' => 'Cámara trasera',           'es_critico' => false],
        ], $now);

        // ─── SECCIÓN 3: Estado general de la carrocería y accesorios ──
        $seccionId = DB::table('checklist_secciones')->insertGetId([
            'plantilla_id'   => $plantillaId,
            'nombre'         => 'Estado general de la carrocería y accesorios',
            'descripcion'    => 'Verificación del funcionamiento y estado de los elementos que componen la carrocería en general.',
            'tipo_respuesta' => 'estado',
            'orden'          => 3,
            'activa'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->insertItems($seccionId, [
            ['nombre' => 'Vidrios',              'es_critico' => false],
            ['nombre' => 'Alzavidrios (man o aut)', 'es_critico' => false],
            ['nombre' => 'Espejos',              'es_critico' => false],
            ['nombre' => 'Parachoques',          'es_critico' => false],
            ['nombre' => 'Pisaderas',            'es_critico' => false],
            ['nombre' => 'Carrocería',           'es_critico' => false],
            ['nombre' => 'Barra antivuelco',     'es_critico' => false],
            ['nombre' => 'Lona marítima',        'es_critico' => false],
            ['nombre' => 'Antenas',              'es_critico' => false],
        ], $now);

        // ─── SECCIÓN 4: Luces ──────────────────────────────
        $seccionId = DB::table('checklist_secciones')->insertGetId([
            'plantilla_id'   => $plantillaId,
            'nombre'         => 'Luces',
            'descripcion'    => 'Probar el funcionamiento de las luces. Si se cambia alguna ampolleta dejar registrado en la caja de observaciones.',
            'tipo_respuesta' => 'estado',
            'orden'          => 4,
            'activa'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->insertItems($seccionId, [
            ['nombre' => 'Luces Bajas',              'es_critico' => true],
            ['nombre' => 'Luces Altas',              'es_critico' => true],
            ['nombre' => 'Luces de Navegación',      'es_critico' => true],
            ['nombre' => 'Luces Intermitentes',      'es_critico' => true],
            ['nombre' => 'Luz de Freno',             'es_critico' => true],
            ['nombre' => 'Luz de Reversa',           'es_critico' => false],
            ['nombre' => 'Luces interiores de cabina', 'es_critico' => false],
        ], $now);

        // ─── SECCIÓN 5: Interior Cabina ────────────────────
        $seccionId = DB::table('checklist_secciones')->insertGetId([
            'plantilla_id'   => $plantillaId,
            'nombre'         => 'Interior Cabina',
            'descripcion'    => 'Inspección y prueba de los elementos al interior de la cabina.',
            'tipo_respuesta' => 'estado',
            'orden'          => 5,
            'activa'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->insertItems($seccionId, [
            ['nombre' => 'Panel de instrumentación',              'es_critico' => true],
            ['nombre' => 'Botoneras',                             'es_critico' => false],
            ['nombre' => 'Telecomandos',                          'es_critico' => false],
            ['nombre' => 'Llave de arranque',                     'es_critico' => true],
            ['nombre' => 'Freno de mano',                         'es_critico' => true],
            ['nombre' => 'Palanca de cambios MT / selector AUT',  'es_critico' => true],
            ['nombre' => 'Selector 4x4',                         'es_critico' => false],
            ['nombre' => 'Estado de asientos',                    'es_critico' => false],
            ['nombre' => 'Cinturones de seguridad',               'es_critico' => true],
            ['nombre' => 'Orden de accesorios',                   'es_critico' => false],
            ['nombre' => 'Bitácora',                              'es_critico' => false],
            ['nombre' => 'Herramientas (llave de rueda, gata, etc.)', 'es_critico' => false],
            ['nombre' => 'Limpieza de cabina',                    'es_critico' => false],
        ], $now);

        // ─── SECCIÓN 6: Documentos ─────────────────────────
        $seccionId = DB::table('checklist_secciones')->insertGetId([
            'plantilla_id'   => $plantillaId,
            'nombre'         => 'Documentos',
            'descripcion'    => 'Si existe algún documento como la RT próxima a vencer, indicar en la caja de observaciones aprox. 30 días antes.',
            'tipo_respuesta' => 'documento',
            'orden'          => 6,
            'activa'         => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->insertItems($seccionId, [
            ['nombre' => 'Padrón',                 'es_critico' => false],
            ['nombre' => 'Permiso de Circulación',  'es_critico' => false],
            ['nombre' => 'SOAP',                    'es_critico' => false],
            ['nombre' => 'Revisión Técnica',        'es_critico' => false],
        ], $now);
    }

    private function insertItems(int $seccionId, array $items, Carbon $now): void
    {
        foreach ($items as $orden => $item) {
            DB::table('checklist_items')->insert([
                'seccion_id'  => $seccionId,
                'nombre'      => $item['nombre'],
                'es_critico'  => $item['es_critico'],
                'orden'       => $orden + 1,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}