<?php

use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────
// AUTH (públicas)
// ─────────────────────────────────────────────────────────────────────────
Route::get('login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::middleware(['rol'])->group(function () {

    // ─────────────────────────────────────────────────────────────────
    // DASHBOARD Y RUTAS COMUNES (todos los roles)
    // ─────────────────────────────────────────────────────────────────
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/guardia-comandante', [App\Http\Controllers\DashboardController::class, 'guardarGuardia'])
        ->name('dashboard.guardia-comandante');

    // Guardias nocturnas — solo lectura (todos los roles)
    Route::get('guardias-nocturnas',           [App\Http\Controllers\GuardiaNocturnaController::class, 'index'])->name('guardias-nocturnas.index');
    Route::get('guardias-nocturnas/{guardia}', [App\Http\Controllers\GuardiaNocturnaController::class, 'show']) ->name('guardias-nocturnas.show');

    // Reportes accesibles para todos los roles
    Route::get('reportes/exportar-cuartelero',  [App\Http\Controllers\ReporteController::class, 'exportarCuartelero'])->name('reportes.exportar-cuartelero');
    Route::get('reportes/salidas/exportar',     [App\Http\Controllers\ReporteSalidaController::class, 'exportar'])   ->name('reportes.salidas.exportar');
    Route::get('reportes/salidas',              [App\Http\Controllers\ReporteSalidaController::class, 'index'])       ->name('reportes.salidas');
    Route::get('reportes/exportar-voluntario',  [App\Http\Controllers\ReporteController::class, 'exportarVoluntario'])->name('reportes.exportar-voluntario');
    Route::get('reportes/exportar',             [App\Http\Controllers\ReporteController::class, 'exportar'])          ->name('reportes.exportar');
    Route::get('reportes',                      [App\Http\Controllers\ReporteController::class, 'index'])             ->name('reportes.index');
    Route::get('consultas',                     [App\Http\Controllers\ConsultaController::class, 'index'])            ->name('consultas.index');
    Route::get('estadisticas',                  [App\Http\Controllers\EstadisticaController::class, 'index'])         ->name('estadisticas.index');
    Route::get('guardias-nocturnas/{guardia}/pdf', [App\Http\Controllers\GuardiaNocturnaController::class, 'exportarPdf'])->name('guardias-nocturnas.pdf');
    Route::get('libro-novedades/{libroNovedade}/pdf', [App\Http\Controllers\LibroNovedadesController::class, 'exportarPdf'])->name('libro-novedades.pdf');
    // ─────────────────────────────────────────────────────────────────
    // OPERACIONES (solo operadores — excluye admin y comandante)
    // ─────────────────────────────────────────────────────────────────
    Route::middleware('rol:operador')->group(function () {

        // Guardias nocturnas — escritura
        Route::prefix('guardias-nocturnas')->name('guardias-nocturnas.')->group(function () {
            Route::post('/iniciar',                            [App\Http\Controllers\GuardiaNocturnaController::class, 'iniciar'])             ->name('iniciar');
            Route::get('/{guardia}/edit',                      [App\Http\Controllers\GuardiaNocturnaController::class, 'edit'])                ->name('edit');
            Route::post('/{guardia}/compania',                 [App\Http\Controllers\GuardiaNocturnaController::class, 'guardarCompania'])     ->name('guardar-compania');
            Route::post('/{guardia}/cerrar',                   [App\Http\Controllers\GuardiaNocturnaController::class, 'cerrar'])              ->name('cerrar');
            Route::get('/{guardia}/heredar/{compania}',        [App\Http\Controllers\GuardiaNocturnaController::class, 'heredar'])             ->name('heredar');
            Route::post('/{guardia}/agregar-voluntario',       [App\Http\Controllers\GuardiaNocturnaController::class, 'agregarVoluntario'])   ->name('agregar-voluntario');
            Route::post('/{guardia}/observacion/{gnCompania}', [App\Http\Controllers\GuardiaNocturnaController::class, 'agregarObservacion'])  ->name('agregar-observacion');
            Route::patch('/voluntario/{gnVoluntario}/hora-salida', [App\Http\Controllers\GuardiaNocturnaController::class, 'registrarHoraSalida'])->name('hora-salida');
        });

        // Libro de novedades
        Route::get('/libro-novedades',                         [App\Http\Controllers\LibroNovedadesController::class, 'index'])  ->name('libro-novedades.index');
        Route::post('/libro-novedades/iniciar',                [App\Http\Controllers\LibroNovedadesController::class, 'iniciar'])->name('libro-novedades.iniciar');
        Route::get('/libro-novedades/{libroNovedade}',         [App\Http\Controllers\LibroNovedadesController::class, 'show'])   ->name('libro-novedades.show');
        Route::get('/libro-novedades/{libroNovedade}/editar',  [App\Http\Controllers\LibroNovedadesController::class, 'edit'])   ->name('libro-novedades.edit');
        Route::put('/libro-novedades/{libroNovedade}',         [App\Http\Controllers\LibroNovedadesController::class, 'update']) ->name('libro-novedades.update');
        Route::post('/libro-novedades/{libroNovedade}/cerrar', [App\Http\Controllers\LibroNovedadesController::class, 'cerrar']) ->name('libro-novedades.cerrar');

        // Citaciones
        Route::get('citaciones',  [App\Http\Controllers\CitacionController::class, 'index'])->name('citaciones.index');
        Route::post('citaciones', [App\Http\Controllers\CitacionController::class, 'store'])->name('citaciones.store');

        // Boletines
        Route::get('boletines',              [App\Http\Controllers\BoletinController::class, 'index'])  ->name('boletines.index');
        Route::get('boletines/create',       [App\Http\Controllers\BoletinController::class, 'create']) ->name('boletines.create');
        Route::post('boletines',             [App\Http\Controllers\BoletinController::class, 'store'])  ->name('boletines.store');
        Route::get('boletines/{boletin}',    [App\Http\Controllers\BoletinController::class, 'show'])   ->name('boletines.show');
        Route::delete('boletines/{boletin}', [App\Http\Controllers\BoletinController::class, 'destroy'])->name('boletines.destroy');

        // Turnos maquinistas
        Route::post('turnos/confirmar', [App\Http\Controllers\RegistroTurnoController::class, 'storeConfirmado'])->name('turnos.confirmar');
        Route::resource('turnos', App\Http\Controllers\RegistroTurnoController::class)->only(['index', 'store', 'show']);
        Route::post('turnos/{turno}/salida',                       [App\Http\Controllers\RegistroTurnoController::class, 'registrarSalida'])->name('turnos.salida');
        Route::delete('turnos/{turno}/quitar-unidad/{unidad}',     [App\Http\Controllers\RegistroTurnoController::class, 'quitarUnidad'])   ->name('turnos.quitar-unidad');

        // Salidas de unidades
        Route::get('salidas/ultimo-km/{unidad}', [App\Http\Controllers\SalidaUnidadController::class, 'ultimoKm'])->name('salidas.ultimo-km');
        Route::post('salidas/retornar-turno-cuartelero-cancelar', function () {
            session()->forget('retomar_turno_cuartelero');
            return redirect()->route('salidas.index');
        })->name('salidas.retornar-turno-cuartelero-cancelar');
        Route::post('salidas/retornar-turno-cuartelero', [App\Http\Controllers\SalidaUnidadController::class, 'retornarTurnoCuartelero'])->name('salidas.retornar-turno-cuartelero');
        Route::resource('salidas', App\Http\Controllers\SalidaUnidadController::class)->only(['index', 'store', 'show']);
        Route::post('salidas/{salida}/llegada', [App\Http\Controllers\SalidaUnidadController::class, 'registrarLlegada'])->name('salidas.llegada');

        // Turnos cuarteleros
        Route::post('cuarteleros-turnos/confirmar',                    [App\Http\Controllers\TurnoCuarteleroController::class, 'storeConfirmado'])->name('cuarteleros.turnos.confirmar');
        Route::get('cuarteleros-turnos',                               [App\Http\Controllers\TurnoCuarteleroController::class, 'index'])          ->name('cuarteleros.turnos');
        Route::post('cuarteleros-turnos',                              [App\Http\Controllers\TurnoCuarteleroController::class, 'store'])           ->name('cuarteleros.turnos.store');
        Route::post('cuarteleros-turnos/{turno}/salida',               [App\Http\Controllers\TurnoCuarteleroController::class, 'registrarSalida'])->name('cuarteleros.turnos.salida');
        Route::delete('cuarteleros-turnos/{turno}/quitar-unidad/{unidad}', [App\Http\Controllers\TurnoCuarteleroController::class, 'quitarUnidad'])->name('cuarteleros.turnos.quitar-unidad');

        // Vouchers combustible
        Route::get('vouchers-combustible/exportar', [App\Http\Controllers\VoucherCombustibleController::class, 'exportar'])->name('vouchers-combustible.exportar');
        Route::resource('vouchers-combustible', App\Http\Controllers\VoucherCombustibleController::class, [
            'parameters' => ['vouchers-combustible' => 'voucher']
        ])->only(['index', 'store', 'edit', 'update']);

    });

    // ─────────────────────────────────────────────────────────────────
    // ADMIN Y COMANDANTE
    // ─────────────────────────────────────────────────────────────────
    Route::middleware('rol:admin,comandante')->group(function () {

        Route::get('reportes/combustible',        [App\Http\Controllers\ReporteController::class, 'combustible'])        ->name('reportes.combustible');
        Route::get('reportes/guardias-nocturnas', [App\Http\Controllers\ReporteController::class, 'guardiasNocturnas'])  ->name('reportes.guardias-nocturnas');

        Route::resource('voluntarios', App\Http\Controllers\VoluntarioController::class);
        Route::post('voluntarios/{voluntario}/autorizar-unidad',   [App\Http\Controllers\VoluntarioController::class, 'autorizarUnidad'])  ->name('voluntarios.autorizar-unidad');
        Route::delete('voluntarios/{voluntario}/revocar-unidad',   [App\Http\Controllers\VoluntarioController::class, 'revocarUnidad'])    ->name('voluntarios.revocar-unidad');
        Route::post('voluntarios/{voluntario}/toggle-autorizante', [App\Http\Controllers\VoluntarioController::class, 'toggleAutorizante'])->name('voluntarios.toggle-autorizante');

        Route::resource('unidades', App\Http\Controllers\UnidadController::class)
            ->parameters(['unidades' => 'unidad']);

        Route::resource('claves-salida', App\Http\Controllers\ClaveSalidaController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::resource('cuarteleros', App\Http\Controllers\CuarteleroController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::post('cuarteleros/{cuartelero}/autorizar-unidad',  [App\Http\Controllers\CuarteleroController::class, 'autorizarUnidad'])->name('cuarteleros.autorizar-unidad');
        Route::delete('cuarteleros/{cuartelero}/revocar-unidad',  [App\Http\Controllers\CuarteleroController::class, 'revocarUnidad'])  ->name('cuarteleros.revocar-unidad');

    });

    // ─────────────────────────────────────────────────────────────────
    // SOLO ADMIN
    // ─────────────────────────────────────────────────────────────────
    Route::middleware('rol:admin')->group(function () {
        Route::resource('companias', App\Http\Controllers\CompaniaController::class);
        Route::resource('usuarios', App\Http\Controllers\UsuarioController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });

});