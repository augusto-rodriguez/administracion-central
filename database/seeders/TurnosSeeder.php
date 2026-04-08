<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TurnosSeeder extends Seeder
{
    // Autorizaciones reales de tu base de datos
    protected $autorizaciones = [
        1 => [1, 2], // Juan Pérez → B-1, R-1
        2 => [1, 2], // María López → B-1, R-1
        3 => [3, 4], // Carlos Muñoz → B-2, H-2
        4 => [3, 4], // Daniela Torres → B-2, H-2
        5 => [5, 6], // Felipe Herrera → B-3, R-3
        6 => [],     // Camila Morales → sin unidades asignadas aún
    ];

    public function run(): void
    {
        $faker = \Faker\Factory::create('es_ES');

        // Generar turnos desde Enero 2024 hasta hoy
        $inicio = Carbon::create(2024, 1, 1);
        $fin    = Carbon::now()->subDays(1);

        $current = $inicio->copy();

        while ($current->lte($fin)) {
            // Entre 2 y 5 turnos por día
            $turnosDia = rand(2, 5);

            // Seleccionar maquinistas aleatorios para ese día (solo los que tienen unidades)
            $maquinistasDisponibles = collect([1, 2, 3, 4, 5])->shuffle()->take($turnosDia);

            foreach ($maquinistasDisponibles as $maquinistaId) {
                $unidades = $this->autorizaciones[$maquinistaId];
                if (empty($unidades)) continue;

                // Hora de entrada aleatoria entre 7:00 y 22:00
                $horaEntrada = $current->copy()->setTime(rand(7, 22), rand(0, 59));

                // Duración entre 30 min y 8 horas
                $duracionMinutos = rand(30, 480);
                $horaSalida = $horaEntrada->copy()->addMinutes($duracionMinutos);

                // No pasar de medianoche
                if ($horaSalida->day !== $horaEntrada->day) {
                    $horaSalida = $horaEntrada->copy()->setTime(23, 59);
                    $duracionMinutos = $horaEntrada->diffInMinutes($horaSalida);
                }

                // Seleccionar 1 o 2 unidades aleatoriamente
                $cantidadUnidades = rand(1, count($unidades));
                $unidadesSeleccionadas = collect($unidades)->shuffle()->take($cantidadUnidades)->values();

                // Insertar turno
                $turnoId = DB::table('registros_turno')->insertGetId([
                    'maquinista_id' => $maquinistaId,
                    'entrada_at'    => $horaEntrada,
                    'salida_at'     => $horaSalida,
                    'total_minutos' => $duracionMinutos,
                    'observaciones' => rand(0, 3) === 0 ? $faker->sentence(4) : null,
                    'created_at'    => $horaEntrada,
                    'updated_at'    => $horaSalida,
                ]);

                // Insertar unidades del turno
                foreach ($unidadesSeleccionadas as $unidadId) {
                    DB::table('turno_unidad')->insert([
                        'turno_id'   => $turnoId,
                        'unidad_id'  => $unidadId,
                        'created_at' => $horaEntrada,
                        'updated_at' => $horaEntrada,
                    ]);
                }
            }

            $current->addDay();
        }

        $this->command->info('✅ Turnos generados desde Enero 2024 hasta hoy.');
    }
}