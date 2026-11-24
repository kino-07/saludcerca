@extends('layouts.sc')
@section('titulo', 'Mis establecimientos')
@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Mis establecimientos</h4>
    <a class="btn btn-success" href="{{ route('representante.create') }}">+ Registrar establecimiento</a>
</div>
<div class="card shadow-sm">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($lista as $e)
            <tr>
                <td>{{ $e->nombre }}<div class="small text-muted">{{ $e->direccion }}</div></td>
                <td>{{ $e->tipo === 'farmacia' ? 'Farmacia' : 'Centro de salud' }}</td>
                <td>
                    @if ($e->estado === 'activo')
                        <span class="badge bg-success">Activo</span>
                    @elseif ($e->estado === 'pendiente_validacion')
                        <span class="badge bg-warning text-dark">Pendiente de validación</span>
                    @else
                        <span class="badge bg-secondary">{{ $e->estado }}</span>
                    @endif
                </td>
                <td class="text-end"><a class="btn btn-sm btn-outline-success" href="{{ route('representante.gestionar', $e) }}">Gestionar</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Aún no tienes establecimientos registrados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
