<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Horario;
use App\Models\Servicio;
use App\Models\Turno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RepresentanteController extends Controller
{
    private const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    // Redirige a cada usuario a su panel según el rol
    public function panel(): RedirectResponse
    {
        $rol = auth()->user()->rol ?? 'ciudadano';

        return match ($rol) {
            'representante' => redirect()->route('representante.index'),
            'administrador' => redirect()->route('admin.index'),
            default => redirect()->route('ciudadano.index'),
        };
    }

    private function soloRepresentante(): void
    {
        abort_unless((auth()->user()->rol ?? '') === 'representante', 403, 'Solo para representantes.');
    }

    private function propio(Establecimiento $e): void
    {
        $this->soloRepresentante();
        abort_unless($e->representante_id === auth()->id(), 403, 'Este establecimiento no es tuyo.');
    }

    // Solo ve los establecimientos que el administrador le asignó
    public function index()
    {
        $this->soloRepresentante();
        $orden = request('orden', 'nombre_asc');
        $lista = Establecimiento::with(['turnos', 'horarios'])
            ->where('representante_id', auth()->id())
            ->when($orden === 'nombre_desc', fn ($q) => $q->orderByDesc('nombre'))
            ->when($orden === 'estado', fn ($q) => $q->orderBy('estado')->orderBy('nombre'))
            ->when(!in_array($orden, ['nombre_desc', 'estado']), fn ($q) => $q->orderBy('nombre'))
            ->get();

        return view('representante.index', ['lista' => $lista, 'orden' => $orden]);
    }

    public function gestionar(Establecimiento $establecimiento)
    {
        $this->propio($establecimiento);
        $establecimiento->load(['horarios', 'turnos', 'servicios', 'fotos']);

        $horarios = [];
        foreach (self::DIAS as $d) {
            $horarios[$d] = $establecimiento->horarios->firstWhere('dia_semana', $d);
        }

        return view('representante.gestionar', [
            'e' => $establecimiento,
            'dias' => self::DIAS,
            'horarios' => $horarios,
            'servicios' => Servicio::orderBy('nombre')->get(),
            'turnos' => request('turnos') === 'asc'
                ? $establecimiento->turnos->sortBy('fecha_inicio')
                : $establecimiento->turnos->sortByDesc('fecha_inicio'),
            'hoy' => now('America/La_Paz')->toDateString(),
        ]);
    }

    // El representante solo cambia teléfono y servicios (el resto lo define el administrador)
    public function actualizarDatos(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->propio($establecimiento);

        $request->merge(['telefono' => preg_replace('/\D/', '', (string) $request->input('telefono', ''))]);
        $v = $request->validate([
            'telefono' => ['nullable', 'regex:/^[0-9]{7,8}$/'],
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ], [
            'telefono.regex' => 'El teléfono debe tener solo números (7 u 8 dígitos).',
        ]);

        $establecimiento->update(['telefono' => ($v['telefono'] ?? '') !== '' ? $v['telefono'] : null]);
        $establecimiento->servicios()->sync($v['servicios'] ?? []);

        return back()->with('ok', 'Teléfono y servicios actualizados.');
    }

    public function guardarHorarios(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->propio($establecimiento);
        $entrada = $request->input('horarios', []);

        $nuevos = [];
        foreach (self::DIAS as $d) {
            $fila = $entrada[$d] ?? [];
            if (!empty($fila['cerrado'])) {
                continue;
            }
            $ap = $fila['abre'] ?? null;
            $ci = $fila['cierra'] ?? null;
            if (!$ap || !$ci) {
                continue;
            }
            if ($ci <= $ap) {
                return back()->withErrors(['horarios' => "En $d la hora de cierre debe ser mayor a la de apertura."])->withInput();
            }
            $nuevos[] = ['establecimiento_id' => $establecimiento->id, 'dia_semana' => $d,
                         'hora_apertura' => $ap, 'hora_cierre' => $ci];
        }

        $establecimiento->horarios()->delete();
        foreach ($nuevos as $n) {
            Horario::create($n);
        }

        return back()->with('ok', 'Horarios guardados.');
    }

    public function turnoStore(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->propio($establecimiento);
        abort_unless($establecimiento->tipo === 'farmacia', 403, 'Solo las farmacias tienen turnos.');

        $hoy = now('America/La_Paz')->toDateString();
        $v = $request->validate([
            'fecha_inicio' => ['required', 'date', 'after_or_equal:' . $hoy],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ], [
            'fecha_inicio.after_or_equal' => 'No se pueden registrar fechas pasadas: el turno debe comenzar hoy o después.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ]);

        $solapa = $establecimiento->turnos()
            ->where('fecha_inicio', '<=', $v['fecha_fin'])
            ->where('fecha_fin', '>=', $v['fecha_inicio'])
            ->exists();
        if ($solapa) {
            return back()->withErrors(['fecha_inicio' => 'Ese periodo se cruza con otro turno ya registrado.'])->withInput();
        }

        Turno::create($v + ['establecimiento_id' => $establecimiento->id]);

        return back()->with('ok', 'Turno registrado.');
    }

    public function turnoDestroy(Establecimiento $establecimiento, Turno $turno): RedirectResponse
    {
        $this->propio($establecimiento);
        abort_unless($turno->establecimiento_id === $establecimiento->id, 404);
        $turno->delete();

        return back()->with('ok', 'Turno eliminado.');
    }
}
