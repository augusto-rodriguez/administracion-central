@extends('layouts.app')
@section('title', 'Claves de Salida')
@section('content')

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-tag me-2"></i>Claves de Salida</h4>
    <a href="{{ route('claves-salida.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva Clave
    </a>
</div>

<div class="row g-3 g-md-4">
    {{-- Emergencias --}}
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white fw-bold">
                <i class="bi bi-exclamation-triangle me-2"></i>Emergencias
            </div>
            <div class="card-body p-0">

                {{-- Tabla desktop --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0 d-none d-md-table">
                        <thead class="table-light">
                            <tr><th width="80">Código</th><th>Descripción</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($emergencias as $clave)
                            <tr>
                                <td><span class="badge bg-danger">{{ $clave->codigo }}</span></td>
                                <td>{{ $clave->descripcion }}</td>
                                <td>
                                    <a href="{{ route('claves-salida.edit', $clave) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Cards móvil --}}
                <div class="d-md-none">
                    @foreach($emergencias as $clave)
                        <div class="border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-danger me-1">{{ $clave->codigo }}</span>
                                <span class="small">{{ $clave->descripcion }}</span>
                            </div>
                            <a href="{{ route('claves-salida.edit', $clave) }}" class="btn btn-sm btn-outline-primary flex-shrink-0 ms-2">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    {{-- Administrativas --}}
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-gear me-2"></i>Administrativas
            </div>
            <div class="card-body p-0">

                {{-- Tabla desktop --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0 d-none d-md-table">
                        <thead class="table-light">
                            <tr><th width="80">Código</th><th>Descripción</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($administrativas as $clave)
                            <tr>
                                <td><span class="badge bg-primary">{{ $clave->codigo }}</span></td>
                                <td>{{ $clave->descripcion }}</td>
                                <td>
                                    <a href="{{ route('claves-salida.edit', $clave) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Cards móvil --}}
                <div class="d-md-none">
                    @foreach($administrativas as $clave)
                        <div class="border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary me-1">{{ $clave->codigo }}</span>
                                <span class="small">{{ $clave->descripcion }}</span>
                            </div>
                            <a href="{{ route('claves-salida.edit', $clave) }}" class="btn btn-sm btn-outline-primary flex-shrink-0 ms-2">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>
@endsection