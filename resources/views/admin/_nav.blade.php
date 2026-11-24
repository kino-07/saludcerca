<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.index') ? 'active bg-success' : 'text-success' }}" href="{{ route('admin.index') }}">Estadísticas</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.establecimientos') ? 'active bg-success' : 'text-success' }}" href="{{ route('admin.establecimientos') }}">Establecimientos</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.reportes') ? 'active bg-success' : 'text-success' }}" href="{{ route('admin.reportes') }}">Reportes</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.usuarios') ? 'active bg-success' : 'text-success' }}" href="{{ route('admin.usuarios') }}">Usuarios</a></li>
</ul>
