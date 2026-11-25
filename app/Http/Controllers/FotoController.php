<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Foto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoController extends Controller
{
    private const MAX_FOTOS = 5;

    // El administrador, o el representante dueño del establecimiento
    private function autorizar(Establecimiento $e): void
    {
        $u = auth()->user();
        $ok = ($u->rol ?? '') === 'administrador'
            || (($u->rol ?? '') === 'representante' && $e->representante_id === $u->id);
        abort_unless($ok, 403, 'No tienes permiso sobre este establecimiento.');
    }

    public function store(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->autorizar($establecimiento);

        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'foto.required' => 'Selecciona una imagen.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'Solo se permiten imágenes JPG, PNG o WEBP.',
            'foto.max' => 'La imagen no puede pesar más de 2 MB.',
            'foto.uploaded' => 'No se pudo subir la imagen (revisa que pese menos de 2 MB).',
        ]);

        if ($establecimiento->fotos()->count() >= self::MAX_FOTOS) {
            return back()->withErrors(['foto' => 'Máximo ' . self::MAX_FOTOS . ' fotos por establecimiento. Elimina una para subir otra.']);
        }

        $ruta = $request->file('foto')->store('establecimientos', 'public');
        Foto::create(['establecimiento_id' => $establecimiento->id, 'ruta' => $ruta]);

        return back()->with('ok', 'Foto subida.');
    }

    public function destroy(Foto $foto): RedirectResponse
    {
        $this->autorizar($foto->establecimiento);

        Storage::disk('public')->delete($foto->ruta);
        $foto->delete();

        return back()->with('ok', 'Foto eliminada.');
    }
}
