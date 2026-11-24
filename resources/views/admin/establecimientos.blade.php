@extends('layouts.sc')
@section('titulo', 'Establecimientos')
@section('contenido')
<h4>Establecimientos</h4>
@include('admin._nav')

<form method="GET" class="mb-3">
    <select name="estado" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
        <option value="">Todos los estados</option>
        <option value="pendiente_validacion" @selected($estado === 'pendiente_validacion')>Pendientes de validación</option>
        <option value="activo" @selected($estado === 'activo')>Activos</option>
        <option value="inactivo" @selected($estado === 'inactivo')>Inactivos</option>
    </select>
</form>

<div class="card shadow-sm">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Representante</th><th>Estado</th><th>Cambiar a</th></tr></thead>
        <tbody>
        @forelse ($lista as $e)
            <tr>
                <td>
                    {{ $e->nombre }}
                    <div class="small text-muted">{{ $e->direccion }}</div>
                </td>
                <td>{{ $e->tipo === 'farmacia' ? 'Farmacia' : 'Centro de salud' }}</td>
                <td>{{ $e->representante->name ?? '—' }}</td>
                <td>
                    @if ($e->estado === 'activo') <span class="badge bg-success">Activo</span>
                    @elseif ($e->estado === 'pendiente_validacion') <span class="badge bg-warning text-dark">Pendiente</span>
                    @else <span class="badge bg-secondary">Inactivo</span> @endif
                </td>
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
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No hay establecimientos.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
