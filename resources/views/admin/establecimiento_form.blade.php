@extends('layouts.sc')
@section('titulo', isset($e) ? 'Editar establecimiento' : 'Nuevo establecimiento')
@section('contenido')
<h4>{{ isset($e) ? 'Editar: ' . $e->nombre : 'Nuevo establecimiento' }}</h4>
@include('admin._nav')
<p class="text-muted small">El representante asignado podrá administrar los horarios, turnos, teléfono y servicios de este establecimiento.</p>
<form method="POST" action="{{ isset($e) ? route('admin.establecimientos.update', $e) : route('admin.establecimientos.store') }}" class="card card-body shadow-sm">
    @csrf
    @if (isset($e)) @method('PUT') @endif
    @include('admin._datos_est')
    <div class="mt-3">
        <button class="btn btn-success">Guardar</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.establecimientos') }}">Cancelar</a>
    </div>
</form>

@if (isset($e))
    <div class="mt-3">@include('partials._fotos')</div>
@endif
@endsection
