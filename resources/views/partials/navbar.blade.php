@php
    $u = auth()->user();
    $sub = null;
    if ($u && ($u->rol ?? '') === 'representante') {
        $nombres = \App\Models\Establecimiento::where('representante_id', $u->id)->orderBy('nombre')->pluck('nombre');
        if ($nombres->isEmpty()) {
            $sub = 'Sin establecimiento asignado';
        } else {
            $sub = $nombres->take(2)->implode(' · ') . ($nombres->count() > 2 ? ' · +' . ($nombres->count() - 2) . ' más' : '');
        }
    } elseif ($u && ($u->rol ?? '') === 'administrador') {
        $sub = 'Administrador';
    }
@endphp
<nav class="navbar navbar-dark bg-success mb-3">
    <div class="container-fluid">
        <a class="navbar-brand lh-1 py-1" href="{{ url('/mapa') }}">
            SaludCerca
            @if ($sub)
                <small class="d-block fs-6 fw-light opacity-75">{{ $sub }}</small>
            @endif
        </a>
        <div class="d-flex gap-2 align-items-center">
            @auth
                <span class="text-white small d-none d-md-inline">{{ $u->name }}</span>
                @unless (request()->routeIs('mapa'))
                    <a class="btn btn-outline-light btn-sm" href="{{ route('mapa') }}">Mapa</a>
                @endunless
                <a class="btn btn-light btn-sm" href="{{ route('panel') }}">Mi panel</a>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-outline-light btn-sm">Salir</button>
                </form>
            @else
                <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Ingresar</a>
                <a class="btn btn-light btn-sm" href="{{ route('register') }}">Registrarme</a>
            @endauth
        </div>
    </div>
</nav>
