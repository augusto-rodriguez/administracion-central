<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatosSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('companias')->truncate();
        DB::table('unidades')->truncate();
        DB::table('voluntarios')->truncate();
        DB::table('voluntario_roles')->truncate();
        DB::table('voluntario_unidad')->truncate();
        DB::table('registros_turno')->truncate();
        DB::table('turno_unidad')->truncate();
        DB::table('cuarteleros')->truncate();
        DB::table('cuartelero_unidad')->truncate();
        DB::table('registros_turno_cuartelero')->truncate();
        DB::table('turno_cuartelero_unidad')->truncate();
        DB::table('salidas_unidad')->truncate();
        DB::table('vouchers_combustible')->truncate();
        DB::table('citaciones')->truncate();
        DB::table('medios_recepcion_citaciones')->truncate();
        DB::table('boletines')->truncate();
        DB::table('boletin_cuarteleros')->truncate();
        DB::table('boletin_maquinistas')->truncate();
        DB::table('libro_novedades')->truncate();
        DB::table('guardia_nocturna')->truncate();
        DB::table('guardia_nocturna_compania')->truncate();
        DB::table('guardia_nocturna_voluntario')->truncate();
        DB::table('guardia_nocturna_unidad')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::statement("SET sql_mode = ''");

        // =====================
        // COMPAÑÍAS
        // =====================
        $companias = [
            ['nombre' => 'Primera Compañía',  'numero' => 1, 'direccion' => 'Av. Pedro Aguirre Cerda 550', 'telefono' => '4121001'],
            ['nombre' => 'Segunda Compañía',  'numero' => 2, 'direccion' => 'Camilo Henríquez 1200',       'telefono' => '4121002'],
            ['nombre' => 'Tercera Compañía',  'numero' => 3, 'direccion' => 'Av. Nahuelbuta 900',           'telefono' => '4121003'],
            ['nombre' => 'Cuarta Compañía',   'numero' => 4, 'direccion' => 'Camino Los Batros 1500',       'telefono' => '4121004'],
        ];

        foreach ($companias as $c) {
            DB::table('companias')->insert([
                ...$c,
                'activa'     => true,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
        }

        $companiasIds = DB::table('companias')->pluck('id', 'numero');

        // =====================
        // UNIDADES
        // =====================
        $unidades = [
            ['compania_id' => $companiasIds[1], 'nombre' => 'B-1',  'patente' => 'BX1111', 'tipo' => 'Bomba'],
            ['compania_id' => $companiasIds[1], 'nombre' => 'BH-1', 'patente' => 'BH1111', 'tipo' => 'Bomba'],
            ['compania_id' => $companiasIds[1], 'nombre' => 'F-1',  'patente' => 'FR1111', 'tipo' => 'Forestal'],
            ['compania_id' => $companiasIds[1], 'nombre' => 'Z-1',  'patente' => 'ZT1111', 'tipo' => 'Cisterna'],
            ['compania_id' => $companiasIds[2], 'nombre' => 'B-2',  'patente' => 'BX2222', 'tipo' => 'Bomba'],
            ['compania_id' => $companiasIds[2], 'nombre' => 'BQ-2', 'patente' => 'BQ2222', 'tipo' => 'Bomba Portaescala'],
            ['compania_id' => $companiasIds[2], 'nombre' => 'RX-2', 'patente' => 'RX2222', 'tipo' => 'Rescate Técnico'],
            ['compania_id' => $companiasIds[3], 'nombre' => 'B-3',  'patente' => 'BX3333', 'tipo' => 'Bomba'],
            ['compania_id' => $companiasIds[3], 'nombre' => 'R-3',  'patente' => 'RS3333', 'tipo' => 'Rescate'],
            ['compania_id' => $companiasIds[3], 'nombre' => 'F-3',  'patente' => 'FR3333', 'tipo' => 'Forestal'],
            ['compania_id' => $companiasIds[3], 'nombre' => 'RX-3', 'patente' => 'RX3333', 'tipo' => 'Rescate Técnico'],
            ['compania_id' => $companiasIds[4], 'nombre' => 'B-4',  'patente' => 'BX4444', 'tipo' => 'Bomba'],
            ['compania_id' => $companiasIds[4], 'nombre' => 'BQ-4', 'patente' => 'BQ4444', 'tipo' => 'Bomba Portaescala'],
        ];

        foreach ($unidades as $u) {
            DB::table('unidades')->insert([
                ...$u,
                'activa'     => true,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
        }

        $unidadesPorCompania = DB::table('unidades')->get()->groupBy('compania_id');

        // =====================
        // MEDIOS RECEPCIÓN CITACIONES
        // =====================
        $medios = ['WhatsApp', 'Correo Electrónico', 'Teléfono', 'Presencial'];
        foreach ($medios as $medio) {
            DB::table('medios_recepcion_citaciones')->insert([
                'nombre'     => $medio,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
        }
        $mediosIds = DB::table('medios_recepcion_citaciones')->pluck('id')->toArray();

        // =====================
        // VOLUNTARIOS
        // =====================
        $nombres   = ['Juan', 'Pedro', 'Carlos', 'Luis', 'Felipe', 'Diego', 'Mario', 'Roberto', 'Cristian', 'Jorge', 'Rodrigo', 'Matías', 'Sebastián', 'Andrés', 'Alejandro'];
        $apellidos = ['Muñoz', 'Soto', 'Rojas', 'Castro', 'Morales', 'Lagos', 'Vega', 'González', 'Pérez', 'Silva', 'Araya', 'Carrasco', 'Fuentes', 'Herrera', 'Méndez'];

        $voluntarios  = [];
        $oficiales    = [];
        $oficialIndex = 0;

        for ($i = 1; $i <= 40; $i++) {
            $companiaId = $companiasIds->random();

            $id = DB::table('voluntarios')->insertGetId([
                'compania_id' => $companiaId,
                'nombre'      => $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)],
                'rut'         => rand(10000000, 24000000) . '-' . rand(0, 9),
                'telefono'    => '9' . rand(10000000, 99999999),
                'activo'      => true,
                'created_at'  => now()->toDateTimeString(),
                'updated_at'  => now()->toDateTimeString(),
            ]);

            DB::table('voluntario_roles')->insert([
                'voluntario_id'           => $id,
                'rol'                     => 'maquinista',
                'activo'                  => true,
                'puede_autorizar_salidas' => false,
                'created_at'              => now()->toDateTimeString(),
                'updated_at'              => now()->toDateTimeString(),
            ]);

            if ($i % 5 === 0) {
                $oficialIndex++;
                $puedeAutorizar = $oficialIndex <= 4;

                DB::table('voluntario_roles')->insert([
                    'voluntario_id'           => $id,
                    'rol'                     => 'oficial',
                    'activo'                  => true,
                    'puede_autorizar_salidas' => $puedeAutorizar,
                    'created_at'              => now()->toDateTimeString(),
                    'updated_at'              => now()->toDateTimeString(),
                ]);

                $oficiales[] = [
                    'id'              => $id,
                    'compania_id'     => $companiaId,
                    'puede_autorizar' => $puedeAutorizar,
                ];
            }

            $voluntarios[] = ['id' => $id, 'compania_id' => $companiaId];
        }

        $oficialesAutorizantes = collect($oficiales)->where('puede_autorizar', true)->values();

        // =====================
        // AUTORIZACIÓN UNIDADES → VOLUNTARIOS
        // =====================
        foreach ($voluntarios as $v) {
            $unidadesComp = $unidadesPorCompania[$v['compania_id']] ?? [];
            foreach ($unidadesComp as $u) {
                DB::table('voluntario_unidad')->insert([
                    'voluntario_id'      => $v['id'],
                    'unidad_id'          => $u->id,
                    'autorizado_por'     => 'Comandante',
                    'fecha_autorizacion' => Carbon::now()->subYear()->toDateString(),
                    'created_at'         => now()->toDateTimeString(),
                    'updated_at'         => now()->toDateTimeString(),
                ]);
            }
        }

        // =====================
        // CUARTELEROS (1 por compañía)
        // =====================
        $nombresCuarteleros = ['Ricardo Muñoz', 'Héctor Soto', 'Osvaldo Rojas', 'Raúl Castro'];
        $cuarteleros        = [];
        $companiaIdsArray   = $companiasIds->values()->toArray();

        foreach ($companiaIdsArray as $index => $companiaId) {
            $id = DB::table('cuarteleros')->insertGetId([
                'compania_id' => $companiaId,
                'nombre'      => $nombresCuarteleros[$index],
                'rut'         => rand(10000000, 24000000) . '-' . rand(0, 9),
                'telefono'    => '9' . rand(10000000, 99999999),
                'activo'      => true,
                'created_at'  => now()->toDateTimeString(),
                'updated_at'  => now()->toDateTimeString(),
            ]);

            $unidadesComp = $unidadesPorCompania[$companiaId] ?? [];
            foreach ($unidadesComp as $u) {
                DB::table('cuartelero_unidad')->insert([
                    'cuartelero_id' => $id,
                    'unidad_id'     => $u->id,
                    'created_at'    => now()->toDateTimeString(),
                    'updated_at'    => now()->toDateTimeString(),
                ]);
            }

            $cuarteleros[] = ['id' => $id, 'compania_id' => $companiaId];
        }

        // =====================
        // RANGO DE FECHAS: 2025 → HOY
        // =====================
        $inicio = Carbon::create(2025, 1, 1);
        $fin    = Carbon::now()->subDay()->endOfDay();

        // =====================
        // TURNOS MAQUINISTAS
        // =====================
        $fecha = $inicio->copy();
        while ($fecha->lte($fin)) {
            $maquinistasDia = collect($voluntarios)->random(rand(1, 3));
            foreach ($maquinistasDia as $m) {
                $entrada  = $fecha->copy()->setHour(rand(1, 23))->setMinute(rand(0, 59))->setSecond(0);
                $duracion = rand(60, 480);
                $salida   = $entrada->copy()->addMinutes($duracion);

                $turnoId = DB::table('registros_turno')->insertGetId([
                    'voluntario_id' => $m['id'],
                    'entrada_at'    => $entrada->toDateTimeString(),
                    'salida_at'     => $salida->toDateTimeString(),
                    'total_minutos' => $duracion,
                    'created_at'    => $entrada->toDateTimeString(),
                    'updated_at'    => $salida->toDateTimeString(),
                ]);

                $unidadesComp     = $unidadesPorCompania[$m['compania_id']];
                $cantidadRand     = rand(1, 10);
                $maxUnidades      = min($unidadesComp->count(), 3);
                $cantUnidades     = match(true) {
                    $cantidadRand <= 6 => 1,
                    $cantidadRand <= 9 => min(2, $maxUnidades),
                    default            => min(3, $maxUnidades),
                };
                $unidadesSeleccionadas = $unidadesComp->shuffle()->take($cantUnidades);

                foreach ($unidadesSeleccionadas as $u) {
                    DB::table('turno_unidad')->insert([
                        'turno_id'   => $turnoId,
                        'unidad_id'  => $u->id,
                        'created_at' => $entrada->toDateTimeString(),
                        'updated_at' => $salida->toDateTimeString(),
                    ]);
                }
            }
            $fecha->addDay();
        }

        // =====================
        // TURNOS CUARTELEROS
        // =====================
        $fecha = $inicio->copy();
        while ($fecha->lte($fin)) {
            foreach ($cuarteleros as $c) {
                if (rand(1, 10) > 9) continue;
                $duracion   = match(true) {
                    rand(1, 10) <= 7 => 720,
                    rand(1, 10) <= 8 => 480,
                    default          => rand(240, 600),
                };
                $horaInicio = rand(1, 10) <= 7 ? rand(6, 8) : rand(20, 22);
                $entrada    = $fecha->copy()->setHour($horaInicio)->setMinute(rand(0, 30))->setSecond(0);
                $salida     = $entrada->copy()->addMinutes($duracion);

                $turnoId = DB::table('registros_turno_cuartelero')->insertGetId([
                    'cuartelero_id' => $c['id'],
                    'entrada_at'    => $entrada->toDateTimeString(),
                    'salida_at'     => $salida->toDateTimeString(),
                    'total_minutos' => $duracion,
                    'observaciones' => null,
                    'created_at'    => $entrada->toDateTimeString(),
                    'updated_at'    => $salida->toDateTimeString(),
                ]);

                $unidadesComp  = $unidadesPorCompania[$c['compania_id']];
                $cantidad      = min(rand(1, 2), $unidadesComp->count());
                $unidadesTurno = $unidadesComp->random($cantidad);

                foreach ($unidadesTurno as $u) {
                    DB::table('turno_cuartelero_unidad')->insert([
                        'turno_id'   => $turnoId,
                        'unidad_id'  => $u->id,
                        'created_at' => $entrada->toDateTimeString(),
                        'updated_at' => $salida->toDateTimeString(),
                    ]);
                }
            }
            $fecha->addDay();
        }

        // =====================
        // SALIDAS
        // =====================
        $codigosExcluidos    = ['10-11', '10-13', '10-14'];
        $claves              = DB::table('claves_salida')->whereNotIn('codigo', $codigosExcluidos)->get();
        $clavesEmergencia    = $claves->where('tipo', 'emergencia')->values();
        $clavesAdministrativa = $claves->where('tipo', 'administrativa')->values();
        $unidadesIds         = DB::table('unidades')->pluck('id');

        $cuarteleroNombres = [];
        foreach ($cuarteleros as $c) {
            $cuarteleroNombres[$c['id']] = DB::table('cuarteleros')->where('id', $c['id'])->value('nombre');
        }

        $fecha = $inicio->copy();
        while ($fecha->lte($fin)) {
            for ($i = 0; $i < rand(3, 7); $i++) {
                $salidaAt = $fecha->copy()->setHour(rand(1, 23))->setMinute(rand(0, 59))->setSecond(0);
                $duracion = rand(10, 120);
                $llegada  = $salidaAt->copy()->addMinutes($duracion);

                $esCuartelero   = rand(1, 10) <= 7;
                $voluntarioId   = null;
                $conductorLibre = null;

                if ($esCuartelero) {
                    $cuartelero     = collect($cuarteleros)->random();
                    $conductorLibre = '[Cuartelero] ' . $cuarteleroNombres[$cuartelero['id']];
                } else {
                    $voluntarioId = collect($voluntarios)->random()['id'];
                }

                $esEmergencia = rand(1, 10) <= 6;
                if ($esEmergencia) {
                    $clave     = $clavesEmergencia->isNotEmpty() ? $clavesEmergencia->random() : $claves->random();
                    $oficialId = null;
                } else {
                    $clave     = $clavesAdministrativa->isNotEmpty() ? $clavesAdministrativa->random() : $claves->random();
                    $oficialId = $oficialesAutorizantes->isNotEmpty() ? $oficialesAutorizantes->random()['id'] : null;
                }

                $alMandoId = collect($voluntarios)->random()['id'];

                DB::table('salidas_unidad')->insert([
                    'unidad_id'         => $unidadesIds->random(),
                    'clave_salida_id'   => $clave->id,
                    'voluntario_id'     => $voluntarioId,
                    'conductor_libre'   => $conductorLibre,
                    'oficial_id'        => $oficialId,
                    'al_mando_id'       => $alMandoId,
                    'direccion'         => $this->direccionAleatoria(),
                    'cantidad_personal' => rand(2, 8),
                    'km_salida'         => rand(50000, 120000),
                    'km_llegada'        => rand(50001, 120200),
                    'km_recorrido'      => rand(1, 20),
                    'salida_at'         => $salidaAt->toDateTimeString(),
                    'llegada_at'        => $llegada->toDateTimeString(),
                    'created_at'        => $salidaAt->toDateTimeString(),
                    'updated_at'        => $llegada->toDateTimeString(),
                ]);
            }
            $fecha->addDay();
        }

        // =====================
        // VOUCHERS COMBUSTIBLE
        // =====================
        $todasUnidades = DB::table('unidades')->get();
        $adminUserId   = DB::table('users')->first()?->id ?? 1;
        $fecha         = $inicio->copy()->startOfMonth();

        while ($fecha->lte($fin)) {
            $precioMes = rand(600, 1000);
            foreach ($todasUnidades as $unidad) {
                $cargas           = rand(1, 3);
                $mesesDesdeInicio = $inicio->diffInMonths($fecha);
                $kmBase           = 50000 + ($unidad->id * 3000) + ($mesesDesdeInicio * rand(200, 500));

                for ($c = 0; $c < $cargas; $c++) {
                    $diasEnMes  = $fecha->daysInMonth;
                    $diaCarga   = rand(1, $diasEnMes);
                    $fechaCarga = $fecha->copy()->setDay($diaCarga);
                    if ($fechaCarga->gt($fin)) continue;

                    $litros = match($unidad->tipo) {
                        'Bomba'           => round(rand(600, 1200) / 10, 3),
                        'Cisterna'        => round(rand(800, 1500) / 10, 3),
                        'Forestal'        => round(rand(400, 800)  / 10, 3),
                        'Rescate'         => round(rand(300, 700)  / 10, 3),
                        'Rescate Técnico' => round(rand(350, 750)  / 10, 3),
                        default           => round(rand(400, 900)  / 10, 3),
                    };

                    $valorUnitario = max(600, min(1000, $precioMes + rand(-50, 50)));
                    $total         = (int) round($litros * $valorUnitario);
                    $kmCarga       = $kmBase + ($c * rand(500, 1500));

                    $cuarteleroComp = collect($cuarteleros)->firstWhere('compania_id', $unidad->compania_id);
                    if ($cuarteleroComp && rand(0, 1)) {
                        $conductor = DB::table('cuarteleros')->where('id', $cuarteleroComp['id'])->value('nombre') . ' (Cuartelero)';
                    } else {
                        $volComp   = collect($voluntarios)->where('compania_id', $unidad->compania_id);
                        $volId     = $volComp->isNotEmpty() ? $volComp->random()['id'] : collect($voluntarios)->random()['id'];
                        $conductor = DB::table('voluntarios')->where('id', $volId)->value('nombre') . ' (Maquinista)';
                    }

                    $numeroVoucher = str_pad($unidad->id . $fecha->month . $fecha->year . $c . rand(10, 99), 10, '0', STR_PAD_LEFT);

                    DB::table('vouchers_combustible')->insert([
                        'fecha_carga'      => $fechaCarga->toDateString(),
                        'unidad_id'        => $unidad->id,
                        'km_carga'         => $kmCarga,
                        'conductor_nombre' => $conductor,
                        'numero_voucher'   => $numeroVoucher,
                        'litros'           => $litros,
                        'valor_unitario'   => $valorUnitario,
                        'total'            => $total,
                        'observaciones'    => null,
                        'registrado_por'   => $adminUserId,
                        'created_at'       => $fechaCarga->toDateTimeString(),
                        'updated_at'       => $fechaCarga->toDateTimeString(),
                    ]);
                }
            }
            $fecha->addMonth();
        }

        // =====================
        // CITACIONES
        // =====================
        $mensajesCitacion = [
            'Se cita a todo el personal disponible para instrucción.',
            'Reunión obligatoria de compañía.',
            'Guardia especial por evento masivo en el sector.',
            'Citación por simulacro programado.',
            'Personal citado para mantenimiento de unidades.',
            'Reunión de directiva, asistencia obligatoria.',
            'Instrucción especial para nuevos integrantes.',
        ];

        // Generar citaciones vigentes y vencidas
        foreach ($companiasIds as $companiaId) {
            for ($i = 0; $i < 5; $i++) {
                // Mezcla de vigentes y vencidas
                $fechaCitacion = rand(0, 1)
                    ? Carbon::now()->addDays(rand(1, 30))   // vigente
                    : Carbon::now()->subDays(rand(1, 60));  // vencida

                DB::table('citaciones')->insert([
                    'compania_id'       => $companiaId,
                    'medio_recepcion_id' => $mediosIds[array_rand($mediosIds)],
                    'mensaje'           => $mensajesCitacion[array_rand($mensajesCitacion)],
                    'fecha_citacion'    => $fechaCitacion->toDateTimeString(),
                    'created_at'        => now()->toDateTimeString(),
                    'updated_at'        => now()->toDateTimeString(),
                ]);
            }
        }

        // =====================
        // LIBRO DE NOVEDADES
        // =====================
        $operadorId = DB::table('users')->first()?->id ?? 1;
        $fecha      = $inicio->copy();

        while ($fecha->lte($fin)) {
            foreach (['dia', 'noche'] as $turno) {
                $horaInicio = $turno === 'dia' ? '08:00:00' : '20:00:00';
                $horaFin    = $turno === 'dia' ? '20:00:00' : '08:00:00';
                $fechaStr   = $fecha->toDateString();

                $cerradoAt = $turno === 'dia'
                    ? $fecha->copy()->setTime(20, rand(0, 10), 0)
                    : $fecha->copy()->setTime(20, rand(0, 10), 0);

                DB::table('libro_novedades')->insert([
                    'fecha'                              => $fechaStr,
                    'turno'                              => $turno,
                    'hora_inicio'                        => $horaInicio,
                    'hora_fin'                           => $horaFin,
                    'operador_id'                        => $operadorId,
                    'operador_turno_anterior'            => null,
                    'maquinistas_al_recibir'             => json_encode([]),
                    'cuarteleros_al_recibir'             => json_encode([]),
                    'unidades_fuera_servicio_al_recibir' => json_encode([]),
                    'maquinistas_al_entregar'            => json_encode([]),
                    'cuarteleros_al_entregar'            => json_encode([]),
                    'unidades_fuera_servicio_al_entregar'=> json_encode([]),
                    'puestas_en_servicio'                => json_encode([]),
                    'salidas_administrativas'            => json_encode([]),
                    'salidas_emergencia'                 => json_encode([]),
                    'novedades_cronologicas'             => null,
                    'observaciones_telecomunicaciones'   => null,
                    'novedades_viper'                    => null,
                    'estado'                             => 'cerrado',
                    'cerrado_por'                        => $operadorId,
                    'cerrado_at'                         => $cerradoAt->toDateTimeString(),
                    'created_at'                         => $fecha->toDateTimeString(),
                    'updated_at'                         => $cerradoAt->toDateTimeString(),
                ]);
            }
            $fecha->addDay();
        }

        // =====================
        // BOLETINES
        // =====================
        $fecha = $inicio->copy();
        Carbon::setLocale('es');

        while ($fecha->lte($fin)) {
            foreach (['am', 'pm'] as $tipo) {

                // Verificar si ya existe (unique fecha+tipo)
                $existe = DB::table('boletines')->where('fecha', $fecha->toDateString())->where('tipo', $tipo)->exists();
                if ($existe) continue;

                $boletinId = DB::table('boletines')->insertGetId([
                    'fecha'        => $fecha->toDateString(),
                    'tipo'         => $tipo,
                    'texto_guardia'=> null,
                    'created_at'   => $fecha->toDateTimeString(),
                    'updated_at'   => $fecha->toDateTimeString(),
                ]);

                // Cuarteleros en turno (1 por compañía aleatoriamente)
                $cuartelerosBoletin = collect($cuarteleros)->random(rand(2, count($cuarteleros)));
                foreach ($cuartelerosBoletin as $c) {
                    DB::table('boletin_cuarteleros')->insert([
                        'boletin_id'    => $boletinId,
                        'cuartelero_id' => $c['id'],
                        'created_at'    => $fecha->toDateTimeString(),
                        'updated_at'    => $fecha->toDateTimeString(),
                    ]);
                }

                // Maquinistas en turno (2-4 voluntarios aleatorios)
                $maquinistasBoletin = collect($voluntarios)->random(rand(2, 4));
                foreach ($maquinistasBoletin as $m) {
                    $unidadesComp  = $unidadesPorCompania[$m['compania_id']];
                    $unidadesMaq   = $unidadesComp->random(min(rand(1, 3), $unidadesComp->count()));
                    $unidadesTexto = $unidadesMaq->pluck('nombre')->implode(' ');
                    $unidadId      = $unidadesMaq->first()->id;

                    // Evitar duplicado voluntario en mismo boletín
                    $dup = DB::table('boletin_maquinistas')
                        ->where('boletin_id', $boletinId)
                        ->where('voluntario_id', $m['id'])
                        ->exists();
                    if ($dup) continue;

                    DB::table('boletin_maquinistas')->insert([
                        'boletin_id'     => $boletinId,
                        'voluntario_id'  => $m['id'],
                        'unidad_id'      => $unidadId,
                        'unidades_texto' => $unidadesTexto,
                        'estado'         => '6-20',
                        'created_at'     => $fecha->toDateTimeString(),
                        'updated_at'     => $fecha->toDateTimeString(),
                    ]);
                }
            }
            $fecha->addDay();
        }

        // =====================
        // GUARDIAS NOCTURNAS (2025 → hoy)
        // =====================
        $fecha = $inicio->copy();

        while ($fecha->lte($fin)) {
            // Evitar duplicado por fecha unique
            $existe = DB::table('guardia_nocturna')->where('fecha', $fecha->toDateString())->exists();
            if ($existe) {
                $fecha->addDay();
                continue;
            }

            $cerradoAt = $fecha->copy()->setTime(1, rand(0, 15), 0);

            $guardiaId = DB::table('guardia_nocturna')->insertGetId([
                'fecha'       => $fecha->toDateString(),
                'estado'      => 'cerrada',
                'cerrado_por' => $operadorId,
                'cerrado_at'  => $cerradoAt->toDateTimeString(),
                'created_at'  => $fecha->copy()->setTime(20, 0, 0)->toDateTimeString(),
                'updated_at'  => $cerradoAt->toDateTimeString(),
            ]);

            foreach ($companiaIdsArray as $companiaId) {
                // 5% de probabilidad de no reportar
                $sinReporte = rand(1, 100) <= 5;

                $gnCompaniaId = DB::table('guardia_nocturna_compania')->insertGetId([
                    'guardia_nocturna_id' => $guardiaId,
                    'compania_id'         => $companiaId,
                    'oficial_a_cargo_id'  => $sinReporte ? null : collect($voluntarios)->where('compania_id', $companiaId)->random()['id'],
                    'cuartelero_id'       => !$sinReporte && rand(0, 1)
                        ? collect($cuarteleros)->firstWhere('compania_id', $companiaId)['id']
                        : null,
                    'sin_reporte'         => $sinReporte,
                    'observaciones'       => null,
                    'created_at'          => $cerradoAt->toDateTimeString(),
                    'updated_at'          => $cerradoAt->toDateTimeString(),
                ]);

                if ($sinReporte) {
                    $fecha->addDay();
                    continue;
                }

                // Voluntarios en guardia (3-8 por compañía)
                $volsCompania = collect($voluntarios)->where('compania_id', $companiaId)->values();
                $cantidad     = min(rand(3, 8), $volsCompania->count());
                $volsGuardia  = $volsCompania->random($cantidad);

                foreach ($volsGuardia as $v) {
                    DB::table('guardia_nocturna_voluntario')->insert([
                        'guardia_nocturna_compania_id' => $gnCompaniaId,
                        'voluntario_id'                => $v['id'],
                        'hora_ingreso'                 => null, // antes del cierre = 01:00
                        'created_at'                   => $cerradoAt->toDateTimeString(),
                        'updated_at'                   => $cerradoAt->toDateTimeString(),
                    ]);
                }

                // Unidades en servicio
                $unidadesComp    = $unidadesPorCompania[$companiaId];
                $cuarteleroComp  = collect($cuarteleros)->firstWhere('compania_id', $companiaId);
                $cuarteleroEnGN  = DB::table('guardia_nocturna_compania')->where('id', $gnCompaniaId)->value('cuartelero_id');

                foreach ($unidadesComp as $u) {
                    // Si hay cuartelero en guardia, 50% de unidades van con él
                    if ($cuarteleroEnGN && rand(0, 1)) {
                        $maquinistaId = null;
                        $cuarteleroId = $cuarteleroEnGN;
                    } else {
                        // Asignar maquinista aleatorio de la compañía o dejar sin conductor
                        $sinConductor = rand(1, 10) <= 2; // 20% sin conductor
                        if ($sinConductor) {
                            $maquinistaId = null;
                            $cuarteleroId = null;
                        } else {
                            $maquinistaId = $volsCompania->random()['id'];
                            $cuarteleroId = null;
                        }
                    }

                    DB::table('guardia_nocturna_unidad')->insert([
                        'guardia_nocturna_compania_id' => $gnCompaniaId,
                        'unidad_id'                    => $u->id,
                        'maquinista_id'                => $maquinistaId,
                        'cuartelero_id'                => $cuarteleroId,
                        'created_at'                   => $cerradoAt->toDateTimeString(),
                        'updated_at'                   => $cerradoAt->toDateTimeString(),
                    ]);
                }
            }

            $fecha->addDay();
        }

        $this->command->info('✅ Seeder ejecutado correctamente para el período 2025 → hoy.');
    }

    private function direccionAleatoria(): string
    {
        $calles = [
            'Av. Pedro Aguirre Cerda', 'Av. San Pedro', 'Av. Nahuelbuta',
            'Av. Los Parques', 'Av. Andalué', 'Camilo Henríquez',
            'Benjamín Subercaseaux', 'Camino Los Batros', 'Camino Lomas Coloradas',
            'Los Canelos', 'Los Lirios', 'Los Aromos', 'Los Coihues',
        ];

        return $calles[array_rand($calles)] . ' ' . rand(100, 3000) . ', San Pedro de la Paz';
    }
}