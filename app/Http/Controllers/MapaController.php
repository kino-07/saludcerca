<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapaController extends Controller
{
    public function index()
    {
        return view('mapa', ['servicios' => Servicio::orderBy('nombre')->get()]);
    }

    // Devuelve los establecimientos activos más cercanos (JSON), con filtros
    public function buscar(Request $request): JsonResponse
    {
        $lat = (float) $request->input('lat', -17.7834);
        $lng = (float) $request->input('lng', -63.1821);

        $query = Establecimiento::activos()
            ->with(['horarios', 'turnos', 'servicios'])
            ->cercanos($lat, $lng);

        if ($request->filled('q')) {
            $query->where('nombre', 'ilike', '%' . $request->input('q') . '%');
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }
        if ($request->filled('tipo_centro')) {
            $query->where('tipo_centro', $request->input('tipo_centro'));
        }
        if ($request->filled('servicio')) {
            $query->whereHas('servicios', fn ($s) => $s->where('servicios.id', $request->input('servicio')));
        }

        $datos = $query->get()->map(fn (Establecimiento $e) => [
            'id' => $e->id,
            'nombre' => $e->nombre,
            'tipo' => $e->tipo,
            'tipo_centro' => $e->tipo_centro,
            'direccion' => $e->direccion,
            'telefono' => $e->telefono,
            'lat' => $e->latitud,
            'lng' => $e->longitud,
            'distancia_km' => round((float) $e->distancia_km, 2),
            'abierto' => $e->estaAbiertoAhora(),
            'de_turno' => $e->tipo === 'farmacia' && $e->estaDeTurno(),
            'servicios' => $e->servicios->pluck('nombre'),
        ]);

        // Estos filtros se calculan con la hora actual, por eso se aplican aquí
        if ($request->boolean('abierto')) {
            $datos = $datos->filter(fn ($e) => $e['abierto']);
        }
        if ($request->boolean('de_turno')) {
            $datos = $datos->filter(fn ($e) => $e['de_turno']);
        }

        return response()->json($datos->values()->take(30));
    }
}
