<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $e->nombre }} - SaludCerca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style> #mini { height: 280px; border-radius: .5rem; } </style>
</head>
<body class="bg-light">
@include('partials.navbar')

<div class="container pb-4">
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $err) <div>{{ $err }}</div> @endforeach
        </div>
    @endif
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h3 class="mb-2">{{ $e->nombre }}</h3>
            <div class="mb-2">
                @if ($e->tipo === 'farmacia')
                    <span class="badge bg-success">Farmacia</span>
                @else
                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $e->tipo_centro ?? 'centro de salud')) }}</span>
                @endif
                @if ($deTurno)
                    <span class="badge bg-warning text-dark">De turno</span>
                @endif
                @if ($abierto)
                    <span class="badge bg-success-subtle text-success-emphasis">Abierto ahora</span>
                @else
                    <span class="badge bg-secondary">Cerrado ahora</span>
                @endif
            </div>
            <p class="mb-1"><strong>Dirección:</strong> {{ $e->direccion }}</p>
            @if ($e->telefono)
                <p class="mb-1"><strong>Teléfono:</strong> {{ $e->telefono }}</p>
            @endif
            @if ($turnoVigente)
                <p class="mb-1"><strong>Turno vigente:</strong>
                    del {{ $turnoVigente->fecha_inicio->format('d/m/Y') }}
                    al {{ $turnoVigente->fecha_fin->format('d/m/Y') }}</p>
            @endif
            <a class="btn btn-success mt-2" target="_blank"
               href="https://www.google.com/maps/dir/?api=1&destination={{ $e->latitud }},{{ $e->longitud }}">
                Cómo llegar
            </a>
            @auth
                <form method="POST" action="{{ route('favorito.toggle', $e) }}" class="d-inline">
                    @csrf
                    <button class="btn {{ $esFavorito ? 'btn-warning' : 'btn-outline-warning' }} mt-2">
                        {{ $esFavorito ? '★ En favoritos (quitar)' : '☆ Agregar a favoritos' }}
                    </button>
                </form>
            @else
                <a class="btn btn-outline-secondary mt-2" href="{{ route('login') }}">Inicia sesión para guardar favoritos</a>
            @endauth
        </div>
    </div>

    @if ($e->fotos->count())
    <div class="row g-2 mb-3">
        @foreach ($e->fotos as $f)
            <div class="col-6 col-md-3">
                <a href="{{ asset('storage/' . $f->ruta) }}" target="_blank">
                    <img src="{{ asset('storage/' . $f->ruta) }}" class="img-fluid rounded shadow-sm" style="height:140px;width:100%;object-fit:cover" alt="Foto de {{ $e->nombre }}">
                </a>
            </div>
        @endforeach
    </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header">Horarios de atención</div>
                <table class="table table-sm mb-0">
                    @foreach ($horarios as $h)
                        <tr>
                            <td class="text-capitalize">{{ $h->dia_semana }}</td>
                            <td>{{ substr($h->hora_apertura, 0, 5) }} - {{ substr($h->hora_cierre, 0, 5) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="card shadow-sm">
                <div class="card-header">Servicios</div>
                <div class="card-body">
                    @forelse ($e->servicios as $s)
                        <span class="badge text-bg-light border">{{ $s->nombre }}</span>
                    @empty
                        <span class="text-muted">Sin servicios registrados.</span>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div id="mini" class="mb-3"></div>
            <div class="card shadow-sm">
                <div class="card-header">¿Información incorrecta?</div>
                <div class="card-body">
                    @auth
                        <form method="POST" action="{{ route('reporte.store', $e) }}">
                            @csrf
                            <textarea name="descripcion" rows="3" class="form-control mb-2"
                                placeholder="Ej.: el horario o el teléfono ya no son correctos">{{ old('descripcion') }}</textarea>
                            <button class="btn btn-outline-danger btn-sm">Enviar reporte</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}">Inicia sesión</a> para reportar un error.
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var lat = {{ $e->latitud }}, lng = {{ $e->longitud }};
    var m = L.map('mini').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(m);
    L.marker([lat, lng]).addTo(m);
</script>
</body>
</html>
