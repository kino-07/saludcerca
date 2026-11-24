@extends('layouts.sc')
@section('titulo', 'Reportes')
@section('contenido')
<h4>Reportes de ciudadanos</h4>
@include('admin._nav')

<form method="GET" class="mb-3">
    <select name="estado" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
        <option value="pendiente" @selected($estado === 'pendiente')>Pendientes</option>
        <option value="resuelto" @selected($estado === 'resuelto')>Resueltos</option>
        <option value="todos" @selected($estado === 'todos')>Todos</option>
    </select>
</form>

<div class="card shadow-sm">
    <ul class="list-group list-group-flush">
        @forelse ($lista as $r)
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $r->establecimiento->nombre }}</strong>
                        <span class="badge {{ $r->estado === 'resuelto' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($r->estado) }}</span>
                        <div>{{ $r->descripcion }}</div>
                        <div class="small text-muted">Por {{ $r->usuario->name }} · {{ $r->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="text-end">
                        <a class="btn btn-sm btn-outline-secondary mb-1" href="{{ route('establecimiento.ficha', $r->establecimiento) }}" target="_blank">Ver ficha</a>
                        @if ($r->estado === 'pendiente')
                            <form method="POST" action="{{ route('admin.reportes.resolver', $r) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-success">Marcar resuelto</button>
                            </form>
                        @endif
                    </div>
                </div>
            </li>
        @empty
            <li class="list-group-item text-muted">No hay reportes en esta categoría.</li>
        @endforelse
    </ul>
</div>
@endsection
