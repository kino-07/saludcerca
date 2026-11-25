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
        $ordenFav = request('orden_fav', 'reciente');
        $ordenRep = request('orden_rep', 'reciente');

        $favoritos = Favorito::with('establecimiento')->where('user_id', auth()->id())->get();
        $favoritos = match ($ordenFav) {
            'antiguo' => $favoritos->sortBy('created_at'),
            'az' => $favoritos->sortBy(fn ($f) => mb_strtolower($f->establecimiento->nombre)),
            'za' => $favoritos->sortByDesc(fn ($f) => mb_strtolower($f->establecimiento->nombre)),
            default => $favoritos->sortByDesc('created_at'),
        };

        $reportes = Reporte::with('establecimiento')->where('user_id', auth()->id())->get();
        $reportes = match ($ordenRep) {
            'antiguo' => $reportes->sortBy('created_at'),
            'estado' => $reportes->sortBy([['estado', 'asc'], ['created_at', 'desc']]),
            default => $reportes->sortByDesc('created_at'),
        };

        return view('ciudadano.index', [
            'favoritos' => $favoritos->values(), 'reportes' => $reportes->values(),
            'ordenFav' => $ordenFav, 'ordenRep' => $ordenRep,
        ]);
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
