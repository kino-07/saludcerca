@extends('layouts.sc')
@section('titulo', 'Registrar establecimiento')
@section('contenido')
<h4>Registrar establecimiento</h4>
<p class="text-muted">Quedará pendiente hasta que el administrador lo valide.</p>
<form method="POST" action="{{ route('representante.store') }}" class="card card-body shadow-sm">
    @csrf
    @include('representante._datos')
    <div class="mt-3">
        <button class="btn btn-success">Guardar</button>
        <a class="btn btn-outline-secondary" href="{{ route('representante.index') }}">Cancelar</a>
    </div>
</form>
@endsection
