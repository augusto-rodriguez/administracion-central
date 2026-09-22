<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecklistConfiguracion;

class ChecklistConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'clave'       => 'horas_para_completar',
                'valor'       => '12',
                'descripcion' => 'Horas disponibles para completar una inspección desde que se inicia',
            ],
            [
                'clave'       => 'max_inspecciones_unidad_dia',
                'valor'       => '1',
                'descripcion' => 'Cantidad máxima de inspecciones completadas por unidad por día',
            ],
            [
                'clave'       => 'emails_notificacion_hallazgos',
                'valor'       => '',
                'descripcion' => 'Correos que reciben notificación al detectar hallazgos (separar con coma). Dejar vacío para desactivar.',
            ],
            [
                'clave'       => 'notificar_solo_criticos',
                'valor'       => 'no',
                'descripcion' => 'Notificar solo hallazgos críticos (si/no). Si es "no", notifica todos.',
            ],
            [
                'clave'       => 'roles_gestion_hallazgos',
                'valor'       => 'admin,comandante,capitan_cia',
                'descripcion' => 'Roles que pueden gestionar hallazgos y recibir asignaciones (separar con coma)',
            ],
            [
                'clave'       => 'notificar_asignacion_hallazgo',
                'valor'       => 'si',
                'descripcion' => 'Enviar correo al usuario cuando se le asigna un hallazgo (si/no)',
            ],
        ];

        foreach ($configs as $config) {
            ChecklistConfiguracion::firstOrCreate(
                ['clave' => $config['clave']],
                $config
            );
        }
    }
}