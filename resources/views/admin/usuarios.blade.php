@extends('layouts.sc')
@section('titulo', 'Usuarios')
@section('contenido')
<h4>Usuarios</h4>
@include('admin._nav')

<form method="POST" action="{{ route('admin.usuarios.store') }}" class="card card-body shadow-sm mb-3">
    @csrf
    <h5>Crear cuenta de representante o administrador</h5>
    <div class="row g-2">
        <div class="col-md-4"><label class="form-label">Nombre completo</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
        <div class="col-md-4"><label class="form-label">Correo</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
        <div class="col-md-4">
            <label class="form-label">Rol</label>
            <select name="rol" class="form-select">
                <option value="representante" @selected(old('rol', 'representante') === 'representante')>Representante</option>
                <option value="administrador" @selected(old('rol') === 'administrador')>Administrador</option>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Contraseña (mínimo 8)</label><input type="password" name="password" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Repetir contraseña</label><input type="password" name="password_confirmation" class="form-control" required></div>
        <div class="col-md-4 d-flex align-items-end"><button class="btn btn-success w-100">Crear cuenta</button></div>
    </div>
    <div class="small text-muted mt-2">Después de crear un representante, asígnale sus establecimientos con «Editar».</div>
</form>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar nombre o correo..."></div>
    <div class="col-md-2">
        <select name="rol" class="form-select form-select-sm">
            <option value="">Todos los roles</option>
            <option value="administrador" @selected($rol === 'administrador')>Administradores</option>
            <option value="representante" @selected($rol === 'representante')>Representantes</option>
            <option value="ciudadano" @selected($rol === 'ciudadano')>Ciudadanos</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="activo" class="form-select form-select-sm">
            <option value="">Activos e inactivos</option>
            <option value="si" @selected($activo === 'si')>Solo activos</option>
            <option value="no" @selected($activo === 'no')>Solo desactivados</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="orden" class="form-select form-select-sm">
            <option value="nombre_asc" @selected($orden === 'nombre_asc')>Nombre A → Z</option>
            <option value="nombre_desc" @selected($orden === 'nombre_desc')>Nombre Z → A</option>
            <option value="reciente" @selected($orden === 'reciente')>Más recientes</option>
            <option value="antiguo" @selected($orden === 'antiguo')>Más antiguos</option>
            <option value="rol" @selected($orden === 'rol')>Por rol</option>
            <option value="estado" @selected($orden === 'estado')>Activos primero</option>
            <option value="estab_desc" @selected($orden === 'estab_desc')>Más establecimientos primero</option>
            <option value="estab_asc" @selected($orden === 'estab_asc')>Menos establecimientos primero</option>
        </select>
    </div>
    <div class="col-md-2 d-grid"><button class="btn btn-sm btn-success">Aplicar</button></div>
</form>
<div class="small text-muted mb-2">{{ $lista->count() }} {{ $lista->count() === 1 ? 'usuario' : 'usuarios' }}</div>

<div class="card shadow-sm">
    <table class="table mb-0 align-middle">
        <thead><tr><th>Nombre</th><th>Rol</th><th>Establecimientos asignados</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
        @foreach ($lista as $u)
            @php $asig = $asignados->get($u->id, collect()); @endphp
            <tr>
                <td>{{ $u->name }}<div class="small text-muted">{{ $u->email }}</div></td>
                <td><span class="badge bg-{{ $u->rol === 'administrador' ? 'danger' : ($u->rol === 'representante' ? 'primary' : 'secondary') }}">{{ $u->rol }}</span></td>
                <td>
                    @if ($u->rol !== 'representante')
                        —
                    @elseif ($asig->isEmpty())
                        <span class="text-muted small">Sin asignar</span>
                    @else
                        @foreach ($asig->take(3) as $a)
                            <span class="badge text-bg-light border">{{ $a->nombre }}</span>
                        @endforeach
                        @if ($asig->count() > 3) <span class="small text-muted">+{{ $asig->count() - 3 }} más</span> @endif
                    @endif
                </td>
                <td>@if ($u->activo) <span class="badge bg-success">Activo</span> @else <span class="badge bg-secondary">Desactivado</span> @endif</td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.usuarios.edit', $u) }}">Editar</a>
                    @if ($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.usuarios.estado', $u) }}" class="d-inline" onsubmit="return confirm('¿{{ $u->activo ? 'Desactivar' : 'Activar' }} esta cuenta?')">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm {{ $u->activo ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta cuenta definitivamente?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    @else
                        <span class="small text-muted ms-1">Tu cuenta</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
