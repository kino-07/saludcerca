<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Favorito;
use App\Models\Reporte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CiudadanoController extends Controller
{
    // Mi espacio: favoritos + reportes enviados
    public function index()
    {
        $favoritos = Favorito::with('establecimiento')
            ->where('user_id', auth()->id())->latest()->get();
        $reportes = Reporte::with('establecimiento')
            ->where('user_id', auth()->id())->latest()->get();

        return view('ciudadano.index', compact('favoritos', 'reportes'));
    }

    // Agrega o quita de favoritos
    public function toggleFavorito(Establecimiento $establecimiento): RedirectResponse
    {
        abort_unless($establecimiento->estado === 'activo', 404);

        $fav = Favorito::where('user_id', auth()->id())
            ->where('establecimiento_id', $establecimiento->id)->first();

        if ($fav) {
            $fav->delete();
            return back()->with('ok', 'Se quitó de tus favoritos.');
        }

        Favorito::create(['user_id' => auth()->id(), 'establecimiento_id' => $establecimiento->id]);

        return back()->with('ok', 'Agregado a tus favoritos.');
    }

    // Reporta información incorrecta
    public function reportar(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        abort_unless($establecimiento->estado === 'activo', 404);

        $datos = $request->validate([
            'descripcion' => 'required|string|min:10|max:1000',
        ], [
            'descripcion.required' => 'Describe qué información es incorrecta.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
        ]);

        $yaPendiente = Reporte::where('user_id', auth()->id())
            ->where('establecimiento_id', $establecimiento->id)
            ->where('estado', 'pendiente')->exists();
        if ($yaPendiente) {
            return back()->withErrors(['descripcion' => 'Ya tienes un reporte pendiente para este establecimiento.']);
        }

        Reporte::create([
            'user_id' => auth()->id(),
            'establecimiento_id' => $establecimiento->id,
            'descripcion' => $datos['descripcion'],
            'estado' => 'pendiente',
        ]);

        return back()->with('ok', 'Reporte enviado. El administrador lo revisará.');
    }
}
