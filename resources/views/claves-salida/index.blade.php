@extends('layouts.app')
@section('title', 'Claves de Salida')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-tag me-2"></i>Claves de Salida</h4>
    <a href="{{ route('claves-salida.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nueva Clave
    </a>
</div>
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white fw-bold">
                <i class="bi bi-exclamation-triangle me-2"></i>Emergencias
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
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
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-gear me-2"></i>Administrativas
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
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
        </div>
    </div>
</div>
@endsection