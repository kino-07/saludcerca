<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Favorito;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function soloAdmin(): void
    {
        abort_unless((auth()->user()->rol ?? '') === 'administrador', 403, 'Solo para administradores.');
    }

    // Estadísticas generales
    public function index()
    {
        $this->soloAdmin();

        $stats = [
            'total' => Establecimiento::count(),
            'farmacias' => Establecimiento::where('tipo', 'farmacia')->count(),
            'centros' => Establecimiento::where('tipo', 'centro_salud')->count(),
            'activos' => Establecimiento::where('estado', 'activo')->count(),
            'pendientes' => Establecimiento::where('estado', 'pendiente_validacion')->count(),
            'inactivos' => Establecimiento::where('estado', 'inactivo')->count(),
            'reportes_pend' => Reporte::where('estado', 'pendiente')->count(),
            'reportes_res' => Reporte::where('estado', 'resuelto')->count(),
            'usuarios' => User::count(),
            'ciudadanos' => User::where('rol', 'ciudadano')->count(),
            'representantes' => User::where('rol', 'representante')->count(),
            'favoritos' => Favorito::count(),
        ];

        $top = Favorito::selectRaw('establecimiento_id, count(*) as total')
            ->groupBy('establecimiento_id')->orderByDesc('total')->limit(5)
            ->with('establecimiento')->get();

        return view('admin.index', compact('stats', 'top'));
    }

    // Validar / activar / desactivar establecimientos
    public function establecimientos(Request $request)
    {
        $this->soloAdmin();
        $estado = $request->input('estado');

        $lista = Establecimiento::with('representante')
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderByRaw("case estado when 'pendiente_validacion' then 0 when 'activo' then 1 else 2 end")
            ->orderBy('nombre')->get();

        return view('admin.establecimientos', compact('lista', 'estado'));
    }

    public function cambiarEstado(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->soloAdmin();
        $v = $request->validate(['estado' => 'required|in:activo,inactivo,pendiente_validacion']);
        $establecimiento->update(['estado' => $v['estado']]);

        return back()->with('ok', "«{$establecimiento->nombre}» ahora está: " . str_replace('_', ' ', $v['estado']) . '.');
    }

    // Reportes de ciudadanos
    public function reportes(Request $request)
    {
        $this->soloAdmin();
        $estado = $request->input('estado', 'pendiente');

        $lista = Reporte::with(['establecimiento', 'usuario'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->latest()->get();

        return view('admin.reportes', compact('lista', 'estado'));
    }

    public function resolverReporte(Reporte $reporte): RedirectResponse
    {
        $this->soloAdmin();
        $reporte->update(['estado' => 'resuelto', 'administrador_id' => auth()->id()]);

        return back()->with('ok', 'Reporte marcado como resuelto.');
    }

    // Usuarios (solo lectura)
    public function usuarios()
    {
        $this->soloAdmin();

        return view('admin.usuarios', ['lista' => User::orderBy('rol')->orderBy('name')->get()]);
    }
}
