<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Favorito;

class FichaController extends Controller
{
    public function show(Establecimiento $establecimiento)
    {
        abort_unless($establecimiento->estado === 'activo', 404);

        $establecimiento->load(['horarios', 'turnos', 'servicios', 'fotos']);

        $orden = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $horarios = $establecimiento->horarios
            ->sortBy(fn ($h) => array_search($h->dia_semana, $orden))
            ->values();

        $turnoVigente = $establecimiento->turnos->first(fn ($t) => $t->estaVigente());

        $esFavorito = auth()->check() && Favorito::where('user_id', auth()->id())
            ->where('establecimiento_id', $establecimiento->id)->exists();

        return view('ficha', [
            'e' => $establecimiento,
            'horarios' => $horarios,
            'turnoVigente' => $turnoVigente,
            'abierto' => $establecimiento->estaAbiertoAhora(),
            'deTurno' => $establecimiento->tipo === 'farmacia' && $turnoVigente !== null,
            'esFavorito' => $esFavorito,
        ]);
    }
}
