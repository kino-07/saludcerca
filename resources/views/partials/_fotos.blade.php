<div class="card card-body shadow-sm mb-3">
    <h5>Fotos <span class="text-muted fs-6">({{ $e->fotos->count() }}/5)</span></h5>
    <div class="row g-2 mb-3">
        @forelse ($e->fotos as $f)
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <img src="{{ asset('storage/' . $f->ruta) }}" class="card-img-top" style="height:120px;object-fit:cover" alt="Foto de {{ $e->nombre }}">
                    <form method="POST" action="{{ route('fotos.destroy', $f) }}" class="p-1" onsubmit="return confirm('¿Eliminar esta foto?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Aún no hay fotos.</div>
        @endforelse
    </div>
    @if ($e->fotos->count() < 5)
        <form method="POST" action="{{ route('fotos.store', $e) }}" enctype="multipart/form-data" class="row g-2">
            @csrf
            <div class="col-md-8"><input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control" required></div>
            <div class="col-md-4"><button class="btn btn-success w-100">Subir foto</button></div>
        </form>
        <div class="small text-muted mt-1">JPG, PNG o WEBP, máximo 2 MB.</div>
    @endif
</div>
