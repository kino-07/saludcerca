@extends('layouts.sc')
@section('titulo', 'Panel del administrador')
@section('contenido')
<h4>Panel del administrador</h4>
@include('admin._nav')

@php
    $tarjetas = [
        ['Establecimientos', $stats['total'], 'bg-success'],
        ['Farmacias', $stats['farmacias'], 'bg-success'],
        ['Centros de salud', $stats['centros'], 'bg-primary'],
        ['Activos', $stats['activos'], 'bg-success'],
        ['Pendientes de validar', $stats['pendientes'], 'bg-warning text-dark'],
        ['Inactivos', $stats['inactivos'], 'bg-secondary'],
        ['Reportes pendientes', $stats['reportes_pend'], 'bg-danger'],
        ['Reportes resueltos', $stats['reportes_res'], 'bg-secondary'],
        ['Usuarios', $stats['usuarios'], 'bg-dark'],
        ['Ciudadanos', $stats['ciudadanos'], 'bg-dark'],
        ['Representantes', $stats['representantes'], 'bg-dark'],
        ['Favoritos guardados', $stats['favoritos'], 'bg-info text-dark'],
    ];
@endphp
<div class="row g-3 mb-4">
    @foreach ($tarjetas as [$titulo, $valor, $clase])
        <div class="col-6 col-md-3">
            <div class="card text-white {{ $clase }} shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold">{{ $valor }}</div>
                    <div class="small">{{ $titulo }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm">
    <div class="card-header">Los 5 establecimientos más guardados en favoritos</div>
    <ul class="list-group list-group-flush">
        @forelse ($top as $t)
            <li class="list-group-item d-flex justify-content-between">
                <span>{{ $t->establecimiento->nombre }}</span>
                <span class="badge bg-success">{{ $t->total }}</span>
            </li>
        @empty
            <li class="list-group-item text-muted">Todavía no hay favoritos.</li>
        @endforelse
    </ul>
</div>
@endsection
