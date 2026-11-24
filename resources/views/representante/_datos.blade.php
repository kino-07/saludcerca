@php $v = fn ($campo, $def = '') => old($campo, isset($e) ? $e->$campo : $def); @endphp
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <select name="tipo" id="tipo" class="form-select">
            <option value="farmacia" @selected($v('tipo') === 'farmacia')>Farmacia</option>
            <option value="centro_salud" @selected($v('tipo') === 'centro_salud')>Centro de salud</option>
        </select>
    </div>
    <div class="col-md-4" id="boxCentro">
        <label class="form-label">Tipo de centro</label>
        <select name="tipo_centro" class="form-select">
            <option value="centro_de_salud" @selected($v('tipo_centro') === 'centro_de_salud')>Centro de salud</option>
            <option value="clinica" @selected($v('tipo_centro') === 'clinica')>Clínica</option>
            <option value="hospital" @selected($v('tipo_centro') === 'hospital')>Hospital</option>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Nombre</label>
        <input name="nombre" class="form-control" value="{{ $v('nombre') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input name="telefono" class="form-control" value="{{ $v('telefono') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Dirección</label>
        <input name="direccion" class="form-control" value="{{ $v('direccion') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Ubicación: haz clic en el mapa para marcarla</label>
        <div id="selmap" class="selmap"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Latitud</label>
        <input name="latitud" id="lat" class="form-control" value="{{ $v('latitud', '-17.7834') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Longitud</label>
        <input name="longitud" id="lng" class="form-control" value="{{ $v('longitud', '-63.1821') }}" required>
    </div>
</div>

@push('scripts')
<script>
    var latI = document.getElementById('lat'), lngI = document.getElementById('lng');
    var lat0 = parseFloat(latI.value) || -17.7834, lng0 = parseFloat(lngI.value) || -63.1821;
    var mp = L.map('selmap').setView([lat0, lng0], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(mp);
    var mk = L.marker([lat0, lng0]).addTo(mp);
    mp.on('click', function (ev) {
        mk.setLatLng(ev.latlng);
        latI.value = ev.latlng.lat.toFixed(7);
        lngI.value = ev.latlng.lng.toFixed(7);
    });
    function ajustarTipo() {
        document.getElementById('boxCentro').style.display =
            document.getElementById('tipo').value === 'farmacia' ? 'none' : 'block';
    }
    document.getElementById('tipo').onchange = ajustarTipo;
    ajustarTipo();
</script>
@endpush
