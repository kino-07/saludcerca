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

    // Redirige a cada usuario a su panel según el rol (después del login)
    public function panel(): RedirectResponse
    {
        $rol = auth()->user()->rol ?? 'ciudadano';

        if ($rol === 'representante') {
            return redirect()->route('representante.index');
        }

        if ($rol === 'administrador') {
            return redirect()->route('admin.index');
        }

        if ($rol === 'ciudadano') {
            return redirect()->route('ciudadano.index');
        }

        return redirect()->route('mapa');
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

    public function index()
    {
        $this->soloRepresentante();
        $lista = Establecimiento::where('representante_id', auth()->id())->orderBy('nombre')->get();

        return view('representante.index', ['lista' => $lista]);
    }

    public function create()
    {
        $this->soloRepresentante();

        return view('representante.create');
    }

    private function reglas(): array
    {
        return [
            'tipo' => 'required|in:farmacia,centro_salud',
            'tipo_centro' => 'nullable|in:hospital,clinica,centro_de_salud',
            'nombre' => 'required|string|max:150',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $this->soloRepresentante();
        $datos = $request->validate($this->reglas());
        if ($datos['tipo'] === 'farmacia') {
            $datos['tipo_centro'] = null;
        }

        $e = Establecimiento::create($datos + [
            'representante_id' => auth()->id(),
            'estado' => 'pendiente_validacion', // el administrador lo valida
        ]);

        return redirect()->route('representante.gestionar', $e)
            ->with('ok', 'Establecimiento registrado. Quedó pendiente de validación del administrador.');
    }

    public function gestionar(Establecimiento $establecimiento)
    {
        $this->propio($establecimiento);
        $establecimiento->load(['horarios', 'turnos', 'servicios']);

        $horarios = [];
        foreach (self::DIAS as $d) {
            $horarios[$d] = $establecimiento->horarios->firstWhere('dia_semana', $d);
        }

        return view('representante.gestionar', [
            'e' => $establecimiento,
            'dias' => self::DIAS,
            'horarios' => $horarios,
            'servicios' => Servicio::orderBy('nombre')->get(),
            'turnos' => $establecimiento->turnos->sortByDesc('fecha_inicio'),
        ]);
    }

    public function actualizarDatos(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->propio($establecimiento);
        $datos = $request->validate($this->reglas());
        if ($datos['tipo'] === 'farmacia') {
            $datos['tipo_centro'] = null;
        }
        $establecimiento->update($datos);
        $establecimiento->servicios()->sync($request->input('servicios', []));

        return back()->with('ok', 'Datos y servicios actualizados.');
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

        $v = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ]);

        // No se permiten turnos que se solapen
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
