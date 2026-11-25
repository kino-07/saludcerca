@extends('layouts.sc')
@section('titulo', 'Establecimientos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Establecimientos</h4>
    <a class="btn btn-success" href="{{ route('admin.establecimientos.create') }}">+ Nuevo establecimiento</a>
</div>
<div class="mt-3">@include('admin._nav')</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar por nombre..."></div>
    <div class="col-md-2">
        <select name="tipo" class="form-select form-select-sm">
            <option value="">Todos los tipos</option>
            <option value="farmacia" @selected($tipo === 'farmacia')>Farmacias</option>
            <option value="centro_salud" @selected($tipo === 'centro_salud')>Centros de salud</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="estado" class="form-select form-select-sm">
            <option value="">Todos los estados</option>
            <option value="activo" @selected($estado === 'activo')>Activos</option>
            <option value="inactivo" @selected($estado === 'inactivo')>Inactivos</option>
            <option value="pendiente_validacion" @selected($estado === 'pendiente_validacion')>Pendientes</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="rep" class="form-select form-select-sm">
            <option value="">Con o sin representante</option>
            <option value="con" @selected($rep === 'con')>Con representante</option>
            <option value="sin" @selected($rep === 'sin')>Sin representante</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="orden" class="form-select form-select-sm">
            <option value="estado" @selected($orden === 'estado')>Orden: pendientes primero</option>
            <option value="nombre_asc" @selected($orden === 'nombre_asc')>Nombre A → Z</option>
            <option value="nombre_desc" @selected($orden === 'nombre_desc')>Nombre Z → A</option>
            <option value="reciente" @selected($orden === 'reciente')>Más recientes</option>
            <option value="antiguo" @selected($orden === 'antiguo')>Más antiguos</option>
        </select>
    </div>
    <div class="col-md-1 d-grid"><button class="btn btn-sm btn-success">Aplicar</button></div>
</form>
<div class="small text-muted mb-2">{{ $lista->count() }} {{ $lista->count() === 1 ? 'resultado' : 'resultados' }}</div>

<div class="card shadow-sm">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Representante</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
        @forelse ($lista as $e)
            <tr>
                <td>{{ $e->nombre }}<div class="small text-muted">{{ $e->direccion }}</div></td>
                <td>{{ $e->tipo === 'farmacia' ? 'Farmacia' : 'Centro de salud' }}</td>
                <td>{{ $e->representante->name ?? '—' }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.estado', $e) }}" class="d-flex gap-1">
                        @csrf @method('PATCH')
                        <select name="estado" class="form-select form-select-sm">
                            <option value="activo" @selected($e->estado === 'activo')>Activo</option>
                            <option value="inactivo" @selected($e->estado === 'inactivo')>Inactivo</option>
                            <option value="pendiente_validacion" @selected($e->estado === 'pendiente_validacion')>Pendiente</option>
                        </select>
                        <button class="btn btn-sm btn-success">OK</button>
                    </form>
                </td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.establecimientos.edit', $e) }}">Editar</a>
                    <form method="POST" action="{{ route('admin.establecimientos.destroy', $e) }}" class="d-inline"
                          onsubmit="return confirm('¿Eliminar este establecimiento con sus horarios, turnos y reportes?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No hay establecimientos.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
