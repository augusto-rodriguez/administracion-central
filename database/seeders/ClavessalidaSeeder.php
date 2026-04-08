<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClavessalidaSeeder extends Seeder
{
    public function run(): void
    {
        $claves = [
            // Administrativas
            ['codigo' => '6-13', 'descripcion' => 'Material mayor se dirige a', 'tipo' => 'administrativa'],
            ['codigo' => '6-14', 'descripcion' => 'Material mayor a cargar combustible', 'tipo' => 'administrativa'],
            ['codigo' => '6-15', 'descripcion' => 'Retirar material desde centro asistencial', 'tipo' => 'administrativa'],
            ['codigo' => '6-17', 'descripcion' => 'Ejercicio', 'tipo' => 'administrativa'],
            ['codigo' => '6-18', 'descripcion' => 'Práctica de conductor', 'tipo' => 'administrativa'],
            ['codigo' => '6-19', 'descripcion' => 'Reabastecimiento de agua', 'tipo' => 'administrativa'],
            // Emergencias
            ['codigo' => '10-0',  'descripcion' => 'Llamado estructural', 'tipo' => 'emergencia'],
            ['codigo' => '10-1',  'descripcion' => 'Llamado de vehículos', 'tipo' => 'emergencia'],
            ['codigo' => '10-2',  'descripcion' => 'Llamado de pastizales / forestal', 'tipo' => 'emergencia'],
            ['codigo' => '10-3',  'descripcion' => 'Llamado a rescate de emergencias', 'tipo' => 'emergencia'],
            ['codigo' => '10-4',  'descripcion' => 'Llamado a rescate vehicular', 'tipo' => 'emergencia'],
            ['codigo' => '10-5',  'descripcion' => 'Llamado Haz-Mat', 'tipo' => 'emergencia'],
            ['codigo' => '10-6',  'descripcion' => 'Llamado a emanación de gases', 'tipo' => 'emergencia'],
            ['codigo' => '10-7',  'descripcion' => 'Llamado eléctrico', 'tipo' => 'emergencia'],
            ['codigo' => '10-8',  'descripcion' => 'Llamado no clasificado', 'tipo' => 'emergencia'],
            ['codigo' => '10-9',  'descripcion' => 'Llamado a otros servicios', 'tipo' => 'emergencia'],
            ['codigo' => '10-10', 'descripcion' => 'Llamado a escombros', 'tipo' => 'emergencia'],
            ['codigo' => '10-11', 'descripcion' => 'Apoyo a aeródromo y/o aeropuertos', 'tipo' => 'emergencia'],
            ['codigo' => '10-12', 'descripcion' => 'Apoyo a otros cuerpos de bomberos', 'tipo' => 'emergencia'],
            ['codigo' => '10-13', 'descripcion' => 'Llamado a atentados terroristas', 'tipo' => 'emergencia'],
            ['codigo' => '10-14', 'descripcion' => 'Llamado a accidentes aéreos', 'tipo' => 'emergencia'],
            ['codigo' => '10-15', 'descripcion' => 'Llamado a simulacro', 'tipo' => 'emergencia'],
            ['codigo' => '10-16', 'descripcion' => 'Derrumbe', 'tipo' => 'emergencia'],
            ['codigo' => '10-17', 'descripcion' => 'Inundación', 'tipo' => 'emergencia'],
            ['codigo' => '10-18', 'descripcion' => 'Emergencia marítima / portuaria', 'tipo' => 'emergencia'],
        ];

        foreach ($claves as $clave) {
            DB::table('claves_salida')->insert(array_merge($clave, [
                'activa'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ Claves de salida cargadas correctamente.');
    }
}