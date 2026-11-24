@extends('layouts.sc')
@section('titulo', 'Usuarios')
@section('contenido')
<h4>Usuarios registrados</h4>
@include('admin._nav')
<div class="card shadow-sm">
    <table class="table mb-0">
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Registro</th></tr></thead>
        <tbody>
        @foreach ($lista as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge bg-{{ $u->rol === 'administrador' ? 'danger' : ($u->rol === 'representante' ? 'primary' : 'secondary') }}">{{ $u->rol }}</span></td>
                <td>{{ $u->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
