{{--
    Guía "Cómo interpretar estos datos" de la cobertura de conductores.
    Incluye el botón y la ventana (modal). Sirve para capitán y comandancia.
--}}
@php
    $umbralCritico  = \App\Services\CoberturaConductoresService::UMBRAL_CRITICO;
    $umbralCubierto = \App\Services\CoberturaConductoresService::UMBRAL_CUBIERTO;

    $secciones = [
        [
            'titulo' => 'Qué muestra',
            'icono'  => 'bi-eye',
            'items'  => [
                [
                    'p' => '¿El porcentaje es de veces o de tiempo?',
                    'r' => 'Es de <strong>tiempo</strong>. Un 25% significa que, en esa franja, las unidades tuvieron conductor una cuarta parte del tiempo. No indica cuántas veces se puso alguien en servicio.',
                ],
                [
                    'p' => '¿Es de todos los carros o de cada uno?',
                    'r' => 'Con <strong>"Todas las unidades"</strong> se muestra el <strong>promedio</strong> de los carros considerados: si hay 4 carros y en una hora solo uno tenía conductor, esa hora marca 25%. Para ver un carro en particular, elígelo en el selector <strong>"Ver"</strong>; el mapa, el resumen y los horarios se recalculan solo para esa unidad.',
                ],
                [
                    'p' => 'Si un carro tiene conductor y los demás no, ¿sale rojo?',
                    'r' => 'Puede salir rojo o amarillo, porque el color es el promedio de todos los carros. Para saber si había <strong>al menos un carro</strong> disponible, pasa el mouse sobre un cuadro en la vista <strong>"Hora por hora"</strong> y revisa el dato "Al menos una unidad".',
                ],
                [
                    'p' => '¿Qué significan los colores?',
                    'r' => '<strong>Rojo:</strong> menos de ' . $umbralCritico . '% del tiempo con conductor. <strong>Amarillo:</strong> cobertura parcial. <strong>Verde:</strong> ' . $umbralCubierto . '% o más. Los cuadros grises no tienen datos (por ejemplo, horas que aún no ocurren en el período).',
                ],
                [
                    'p' => '¿Por qué el promedio puede verse bajo aunque la compañía esté bien?',
                    'r' => 'Porque el promedio mezcla <strong>todos los carros activos</strong> de la compañía, y no todos suelen tener conductor la misma cantidad de horas. Ejemplo: si una compañía tiene dos carros, uno con conductor todo el día (100%) y otro que casi nunca tiene conductor asignado (0%), el promedio da <strong>50%</strong>, aunque siempre hubo un carro disponible. Por eso conviene revisar también el detalle por unidad y el dato "Al menos una unidad". Los carros fuera de servicio no se consideran en el cálculo.',
                ],
            ],
        ],
        [
            'titulo' => 'De dónde salen los datos',
            'icono'  => 'bi-database',
            'items'  => [
                [
                    'p' => '¿Qué registros se usan?',
                    'r' => 'Solo los de <strong>"Puestas en servicio"</strong> de maquinistas y cuarteleros: hora de entrada, hora de salida y unidades asignadas. Una unidad está cubierta mientras exista un turno abierto con esa unidad asignada.',
                ],
                [
                    'p' => '¿Cuenta si un maquinista de otra compañía sacó el carro?',
                    'r' => 'Sí. Se cuenta <strong>cualquier conductor</strong> registrado con la unidad, sea o no de la misma compañía.',
                ],
                [
                    'p' => 'Si un maquinista y un cuartelero están a la vez con el mismo carro, ¿se cuenta doble?',
                    'r' => 'No. Ese tiempo se cuenta <strong>una sola vez</strong> en la cobertura del carro.',
                ],
                [
                    'p' => '¿Qué unidades se consideran?',
                    'r' => 'Solo las que están <strong>activas hoy</strong> en el sistema. Una unidad dada de baja o marcada fuera de servicio no aparece.',
                ],
            ],
        ],
        [
            'titulo' => 'Situaciones que pueden alterar los resultados',
            'icono'  => 'bi-exclamation-triangle',
            'items'  => [
                [
                    'p' => 'Sale "cubierto" un horario en que no había nadie',
                    'r' => 'Lo más probable es que alguien <strong>no registró su salida</strong> y el turno quedó abierto. El sistema lo considera en servicio hasta que se cierre, lo que <strong>infla</strong> la cobertura.',
                ],
                [
                    'p' => 'Un conductor asignado a varios carros los cubre a todos',
                    'r' => 'Si un maquinista se pone en servicio con dos o más unidades, <strong>todas</strong> cuentan como cubiertas, aunque en la práctica solo pueda conducir una a la vez. Esto también <strong>infla</strong> la cobertura.',
                ],
                [
                    'p' => 'Un maquinista estuvo en el cuartel y no aparece',
                    'r' => 'Si no se registró su entrada en "Puestas en servicio", para el sistema <strong>no estuvo</strong>. Esto <strong>baja</strong> la cobertura.',
                ],
                [
                    'p' => 'Un carro estuvo en el taller y sale sin conductor',
                    'r' => 'El sistema no guarda el historial de cuándo una unidad estuvo fuera de servicio; solo sabe si está activa hoy. Esos días aparecerán como <strong>sin conductor</strong>.',
                ],
                [
                    'p' => 'Con "Últimos 7 días" los resultados cambian mucho',
                    'r' => 'Con 7 días cada cuadro representa <strong>un solo día</strong> (por ejemplo, el último martes), así que una semana atípica pesa mucho. Para ver patrones habituales conviene usar 30 días o más.',
                ],
            ],
        ],
    ];
@endphp

<button type="button" class="btn btn-sm btn-outline-secondary text-nowrap"
        data-bs-toggle="modal" data-bs-target="#modalGuiaCobertura">
    <i class="bi bi-info-circle me-1"></i>Cómo interpretar estos datos
</button>

<div class="modal fade" id="modalGuiaCobertura" tabindex="-1" aria-labelledby="modalGuiaCoberturaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title mb-0" id="modalGuiaCoberturaTitulo">
                    <i class="bi bi-info-circle me-2"></i>Cómo interpretar estos datos
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    La cobertura indica qué parte del tiempo las unidades tuvieron un maquinista o cuartelero
                    puesto en servicio. Es tan confiable como los registros que se hacen en el sistema:
                    revisa estos puntos antes de sacar conclusiones.
                </p>

                @foreach($secciones as $i => $seccion)
                    <h6 class="fw-bold mt-3 mb-2">
                        <i class="bi {{ $seccion['icono'] }} me-1"></i>{{ $seccion['titulo'] }}
                    </h6>
                    <div class="accordion" id="guiaCobertura{{ $i }}">
                        @foreach($seccion['items'] as $j => $item)
                            @php $idItem = "guiaCob{$i}_{$j}"; @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-2 small fw-semibold" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $idItem }}"
                                            aria-expanded="false" aria-controls="{{ $idItem }}">
                                        {{ $item['p'] }}
                                    </button>
                                </h2>
                                <div id="{{ $idItem }}" class="accordion-collapse collapse" data-bs-parent="#guiaCobertura{{ $i }}">
                                    <div class="accordion-body small py-2">{!! $item['r'] !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>