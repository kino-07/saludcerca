@extends('layouts.sc')
@section('titulo', 'Gestionar ' . $e->nombre)
@section('contenido')
<a href="{{ route('representante.index') }}" class="small">&larr; Mis establecimientos</a>
<h4 class="mt-1">{{ $e->nombre }}
    @if ($e->estado === 'activo') <span class="badge bg-success fs-6">Activo</span>
    @else <span class="badge bg-warning text-dark fs-6">{{ str_replace('_', ' ', $e->estado) }}</span> @endif
</h4>

{{-- 1. Datos y servicios --}}
<form method="POST" action="{{ route('representante.datos', $e) }}" class="card card-body shadow-sm mb-3">
    @csrf @method('PUT')
    <h5>Datos y servicios</h5>
    @include('representante._datos')
    <div class="mt-3">
        <label class="form-label">Servicios que ofrece</label><br>
        @php $mios = $e->servicios->pluck('id')->all(); @endphp
        @foreach ($servicios as $s)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="servicios[]" value="{{ $s->id }}" id="s{{ $s->id }}" @checked(in_array($s->id, $mios))>
                <label class="form-check-label" for="s{{ $s->id }}">{{ $s->nombre }}</label>
            </div>
        @endforeach
    </div>
    <div class="mt-3"><button class="btn btn-success">Guardar datos</button></div>
</form>

{{-- 2. Horarios --}}
<form method="POST" action="{{ route('representante.horarios', $e) }}" class="card card-body shadow-sm mb-3">
    @csrf
    <h5>Horarios de atención</h5>
    <table class="table table-sm align-middle">
        <thead><tr><th>Día</th><th>Abre</th><th>Cierra</th><th>Cerrado</th></tr></thead>
        <tbody>
        @foreach ($dias as $d)
            @php $h = $horarios[$d]; @endphp
            <tr>
                <td class="text-capitalize">{{ $d }}</td>
                <td><input type="time" class="form-control form-control-sm" name="horarios[{{ $d }}][abre]" value="{{ $h ? substr($h->hora_apertura, 0, 5) : '08:00' }}"></td>
                <td><input type="time" class="form-control form-control-sm" name="horarios[{{ $d }}][cierra]" value="{{ $h ? substr($h->hora_cierre, 0, 5) : '20:00' }}"></td>
                <td><input type="checkbox" class="form-check-input" name="horarios[{{ $d }}][cerrado]" value="1" @checked(!$h)></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div><button class="btn btn-success">Guardar horarios</button></div>
</form>

{{-- 3. Turnos (solo farmacias) --}}
@if ($e->tipo === 'farmacia')
<div class="card card-body shadow-sm">
    <h5>Turnos de farmacia</h5>
    <form method="POST" action="{{ route('representante.turnos.store', $e) }}" class="row g-2 mb-3">
        @csrf
        <div class="col-md-4"><label class="form-label">Desde</label><input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio') }}" required></div>
        <div class="col-md-4"><label class="form-label">Hasta</label><input type="date" name="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}" required></div>
        <div class="col-md-4 d-flex align-items-end"><button class="btn btn-success">Registrar turno</button></div>
    </form>
    <table class="table table-sm mb-0">
        <thead><tr><th>Desde</th><th>Hasta</th><th></th></tr></thead>
        <tbody>
        @forelse ($turnos as $t)
            <tr>
                <td>{{ $t->fecha_inicio->format('d/m/Y') }}</td>
                <td>{{ $t->fecha_fin->format('d/m/Y') }}</td>
                <td class="text-end">
                    <form method="POST" action="{{ route('representante.turnos.destroy', [$e, $t]) }}" onsubmit="return confirm('¿Eliminar este turno?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-muted">Sin turnos registrados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endif
@endsection
