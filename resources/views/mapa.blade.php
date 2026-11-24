<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SaludCerca - Mapa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #map { height: 75vh; border-radius: .5rem; }
        #lista { max-height: 75vh; overflow-y: auto; }
        .item { cursor: pointer; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-success mb-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/mapa') }}">SaludCerca</a>
        <div>
            @auth
                <a class="btn btn-light btn-sm" href="{{ url('/dashboard') }}">Mi panel</a>
            @else
                <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Ingresar</a>
                <a class="btn btn-light btn-sm" href="{{ route('register') }}">Registrarme</a>
            @endauth
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row g-2 mb-3">
        <div class="col-md-5">
            <input id="q" class="form-control" placeholder="Buscar por nombre...">
        </div>
        <div class="col-md-3">
            <select id="tipo" class="form-select">
                <option value="">Farmacias y centros de salud</option>
                <option value="farmacia">Solo farmacias</option>
                <option value="centro_salud">Solo centros de salud</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button id="btnBuscar" class="btn btn-success">Buscar</button>
        </div>
        <div class="col-md-2 d-grid">
            <button id="btnUbicar" class="btn btn-outline-success">Usar mi ubicación</button>
        </div>
    </div>
    <div class="row g-2 mb-2 align-items-center">
        <div class="col-md-3">
            <select id="tipo_centro" class="form-select form-select-sm">
                <option value="">Todo tipo de centro</option>
                <option value="hospital">Hospital</option>
                <option value="clinica">Clínica</option>
                <option value="centro_de_salud">Centro de salud</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="servicio" class="form-select form-select-sm">
                <option value="">Todos los servicios</option>
                @foreach ($servicios as $s)
                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto form-check ms-2">
            <input class="form-check-input" type="checkbox" id="abierto">
            <label class="form-check-label" for="abierto">Abierto ahora</label>
        </div>
        <div class="col-auto form-check ms-2">
            <input class="form-check-input" type="checkbox" id="de_turno">
            <label class="form-check-label" for="de_turno">De turno</label>
        </div>
    </div>
    <div id="aviso" class="small text-muted mb-2"></div>

    <div class="row g-3">
        <div class="col-lg-8"><div id="map"></div></div>
        <div class="col-lg-4"><div id="lista" class="list-group"></div></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var URL_API = "{{ url('/api/establecimientos') }}";
    var URL_FICHA = "{{ url('/establecimiento') }}/";
    var pos = { lat: -17.7834, lng: -63.1821 }; // Plaza 24 de Septiembre (por defecto)
    var map = L.map('map').setView([pos.lat, pos.lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var miMarcador = L.circleMarker([pos.lat, pos.lng], { radius: 9, color: '#0d6efd', fillOpacity: 0.8 })
        .addTo(map).bindPopup('Tu ubicación');
    var capa = L.layerGroup().addTo(map);
    var marcadores = {};

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function etiquetas(e) {
        var h = '';
        h += e.tipo === 'farmacia'
            ? '<span class="badge bg-success">Farmacia</span> '
            : '<span class="badge bg-primary">Centro de salud</span> ';
        if (e.de_turno) h += '<span class="badge bg-warning text-dark">De turno</span> ';
        h += e.abierto
            ? '<span class="badge bg-success-subtle text-success-emphasis">Abierto ahora</span>'
            : '<span class="badge bg-secondary">Cerrado</span>';
        return h;
    }

    function popup(e) {
        var ruta = 'https://www.google.com/maps/dir/?api=1&origin=' + pos.lat + ',' + pos.lng +
                   '&destination=' + e.lat + ',' + e.lng;
        return '<strong>' + esc(e.nombre) + '</strong><br>' + etiquetas(e) +
               '<br>' + esc(e.direccion) +
               (e.telefono ? '<br>Tel: ' + esc(e.telefono) : '') +
               '<br>A ' + e.distancia_km + ' km' +
               '<br><a href="' + URL_FICHA + e.id + '">Ver ficha</a> · ' +
               '<a href="' + ruta + '" target="_blank">Cómo llegar</a>';
    }

    function buscar() {
        var params = new URLSearchParams({
            lat: pos.lat, lng: pos.lng,
            q: document.getElementById('q').value,
            tipo: document.getElementById('tipo').value,
            tipo_centro: document.getElementById('tipo_centro').value,
            servicio: document.getElementById('servicio').value,
            abierto: document.getElementById('abierto').checked ? 1 : 0,
            de_turno: document.getElementById('de_turno').checked ? 1 : 0
        });
        fetch(URL_API + '?' + params.toString())
            .then(function (r) { return r.json(); })
            .then(function (datos) {
                capa.clearLayers();
                marcadores = {};
                var lista = document.getElementById('lista');
                lista.innerHTML = '';
                if (datos.length === 0) {
                    lista.innerHTML = '<div class="list-group-item">Sin resultados.</div>';
                    return;
                }
                datos.forEach(function (e) {
                    var m = L.marker([e.lat, e.lng]).addTo(capa).bindPopup(popup(e));
                    marcadores[e.id] = m;
                    var div = document.createElement('div');
                    div.className = 'list-group-item item';
                    div.innerHTML = '<div class="fw-bold">' + esc(e.nombre) + '</div>' +
                        '<div class="small">' + etiquetas(e) + '</div>' +
                        '<div class="small text-muted">' + e.distancia_km + ' km · ' + esc(e.direccion) + '</div>' +
                        '<a class="small" href="' + URL_FICHA + e.id + '" onclick="event.stopPropagation()">Ver ficha</a>';
                    div.onclick = function () {
                        map.setView([e.lat, e.lng], 16);
                        m.openPopup();
                    };
                    lista.appendChild(div);
                });
            })
            .catch(function () {
                document.getElementById('aviso').textContent = 'No se pudo cargar la información.';
            });
    }

    function ubicar() {
        var aviso = document.getElementById('aviso');
        if (!navigator.geolocation) {
            aviso.textContent = 'Tu navegador no permite geolocalización. Se usa el centro de la ciudad.';
            return buscar();
        }
        navigator.geolocation.getCurrentPosition(function (p) {
            pos = { lat: p.coords.latitude, lng: p.coords.longitude };
            miMarcador.setLatLng([pos.lat, pos.lng]);
            map.setView([pos.lat, pos.lng], 14);
            aviso.textContent = 'Mostrando lo más cercano a tu ubicación.';
            buscar();
        }, function () {
            aviso.textContent = 'No se pudo obtener tu ubicación. Se usa el centro de Santa Cruz.';
            buscar();
        });
    }

    document.getElementById('btnBuscar').onclick = buscar;
    document.getElementById('btnUbicar').onclick = ubicar;
    document.getElementById('q').addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') buscar();
    });
    ['tipo', 'tipo_centro', 'servicio', 'abierto', 'de_turno'].forEach(function (id) {
        document.getElementById(id).onchange = buscar;
    });

    buscar();
</script>
</body>
</html>
