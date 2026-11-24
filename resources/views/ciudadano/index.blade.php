@extends('layouts.sc')
@section('titulo', 'Mi espacio')
@section('contenido')
<h4>Mi espacio</h4>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">★ Mis favoritos</div>
            <ul class="list-group list-group-flush">
                @forelse ($favoritos as $f)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('establecimiento.ficha', $f->establecimiento) }}">{{ $f->establecimiento->nombre }}</a>
                            <div class="small text-muted">{{ $f->establecimiento->direccion }}</div>
                        </div>
                        <form method="POST" action="{{ route('favorito.toggle', $f->establecimiento) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Quitar</button>
                        </form>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aún no tienes favoritos. Agrégalos desde la ficha de un establecimiento.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">Mis reportes</div>
            <ul class="list-group list-group-flush">
                @forelse ($reportes as $r)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $r->establecimiento->nombre }}</strong>
                            <span class="badge {{ $r->estado === 'resuelto' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($r->estado) }}</span>
                        </div>
                        <div class="small">{{ $r->descripcion }}</div>
                        <div class="small text-muted">{{ $r->created_at->format('d/m/Y H:i') }}</div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No has enviado reportes.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
<a class="btn btn-success mt-3" href="{{ route('mapa') }}">Ir al mapa</a>
@endsection
