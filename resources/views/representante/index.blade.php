@extends('layouts.sc')
@section('titulo', 'Mis establecimientos')
@section('contenido')
<h4>Mis establecimientos</h4>
<form method="GET" class="mb-2">
    <select name="orden" class="form-select form-select-sm w-auto d-inline-block" onchange="this.form.submit()">
        <option value="nombre_asc" @selected($orden === 'nombre_asc')>Nombre A → Z</option>
        <option value="nombre_desc" @selected($orden === 'nombre_desc')>Nombre Z → A</option>
        <option value="estado" @selected($orden === 'estado')>Por estado</option>
    </select>
</form>
<p class="text-muted">Aquí solo aparecen los establecimientos que el administrador te asignó. Si necesitas registrar una nueva sucursal, comunícate con el administrador.</p>
<div class="card shadow-sm">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Situación</th><th></th></tr></thead>
        <tbody>
        @forelse ($lista as $e)
            <tr>
                <td>{{ $e->nombre }}<div class="small text-muted">{{ $e->direccion }}</div></td>
                <td>{{ $e->tipo === 'farmacia' ? 'Farmacia' : 'Centro de salud' }}</td>
                <td>
                    @if ($e->estado === 'activo')
                        <span class="badge bg-success">Visible en el mapa</span>
                    @elseif ($e->estado === 'pendiente_validacion')
                        <span class="badge bg-warning text-dark">Pendiente de validación</span>
                    @else
                        <span class="badge bg-secondary">Inactivo</span>
                    @endif
                    @if ($e->tipo === 'farmacia' && $e->estaDeTurno())
                        <span class="badge bg-warning text-dark">De turno</span>
                    @endif
                    @if ($e->estaAbiertoAhora())
                        <span class="badge bg-success-subtle text-success-emphasis">Abierto ahora</span>
                    @endif
                </td>
                <td class="text-end"><a class="btn btn-sm btn-outline-success" href="{{ route('representante.gestionar', $e) }}">Gestionar</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Todavía no tienes establecimientos asignados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
