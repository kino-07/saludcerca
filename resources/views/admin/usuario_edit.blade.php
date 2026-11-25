@extends('layouts.sc')
@section('titulo', 'Editar usuario')
@section('contenido')
<a href="{{ route('admin.usuarios') }}" class="small">&larr; Usuarios</a>
<h4 class="mt-1">Editar: {{ $u->name }}</h4>

<form method="POST" action="{{ route('admin.usuarios.update', $u) }}" class="card card-body shadow-sm mb-3">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Nombre completo</label><input name="name" class="form-control" value="{{ old('name', $u->name) }}" required></div>
        <div class="col-md-4"><label class="form-label">Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $u->email) }}" required></div>
        <div class="col-md-4">
            <label class="form-label">Rol</label>
            <select name="rol" id="rol" class="form-select" @disabled($u->id === auth()->id())>
                @foreach (['ciudadano' => 'Ciudadano', 'representante' => 'Representante', 'administrador' => 'Administrador'] as $val => $txt)
                    <option value="{{ $val }}" @selected(old('rol', $u->rol) === $val)>{{ $txt }}</option>
                @endforeach
            </select>
            @if ($u->id === auth()->id())
                <input type="hidden" name="rol" value="{{ $u->rol }}">
                <div class="form-text">No puedes cambiar tu propio rol.</div>
            @endif
        </div>
        <div class="col-md-6"><label class="form-label">Nueva contraseña (opcional, mínimo 8)</label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
        <div class="col-md-6"><label class="form-label">Repetir nueva contraseña</label><input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"></div>
    </div>

    <div id="boxEstab" class="mt-4">
        <h5>Establecimientos asignados</h5>
        <p class="text-muted small">Marca los que este representante podrá administrar. Si marcas uno que pertenece a otro representante, se le quitará a ese y pasará a este.</p>
        <div class="row">
            @foreach ($todos as $t)
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="establecimientos[]" value="{{ $t->id }}" id="e{{ $t->id }}"
                               @checked(in_array($t->id, old('establecimientos', $todos->where('representante_id', $u->id)->pluck('id')->all())))>
                        <label class="form-check-label" for="e{{ $t->id }}">
                            {{ $t->nombre }}
                            @if ($t->representante_id && $t->representante_id !== $u->id)
                                <span class="small text-muted">(ahora de {{ $t->representante->name }})</span>
                            @endif
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-success">Guardar cambios</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.usuarios') }}">Cancelar</a>
    </div>
</form>

@if ($u->id !== auth()->id())
    <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" onsubmit="return confirm('¿Eliminar esta cuenta definitivamente? Sus establecimientos quedarán sin asignar.')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger">Eliminar esta cuenta</button>
    </form>
@endif

@push('scripts')
<script>
    var rolSel = document.getElementById('rol');
    function mostrarEstab() {
        document.getElementById('boxEstab').style.display = rolSel.value === 'representante' ? 'block' : 'none';
    }
    rolSel.onchange = mostrarEstab;
    mostrarEstab();
</script>
@endpush
@endsection
