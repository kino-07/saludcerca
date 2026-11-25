<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Favorito;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function soloAdmin(): void
    {
        abort_unless((auth()->user()->rol ?? '') === 'administrador', 403, 'Solo para administradores.');
    }

    // ---------- Estadísticas ----------
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

    // ---------- Establecimientos (sucursales) ----------
    public function establecimientos(Request $request)
    {
        $this->soloAdmin();
        $estado = $request->input('estado');
        $tipo = $request->input('tipo');
        $rep = $request->input('rep');
        $orden = $request->input('orden', 'estado');
        $q = trim((string) $request->input('q', ''));

        $consulta = Establecimiento::with('representante')
            ->when($estado, fn ($x) => $x->where('estado', $estado))
            ->when($tipo, fn ($x) => $x->where('tipo', $tipo))
            ->when($rep === 'sin', fn ($x) => $x->whereNull('representante_id'))
            ->when($rep === 'con', fn ($x) => $x->whereNotNull('representante_id'))
            ->when($q !== '', fn ($x) => $x->where('nombre', 'ilike', '%' . $q . '%'));

        match ($orden) {
            'nombre_asc' => $consulta->orderBy('nombre'),
            'nombre_desc' => $consulta->orderByDesc('nombre'),
            'reciente' => $consulta->orderByDesc('created_at'),
            'antiguo' => $consulta->orderBy('created_at'),
            default => $consulta->orderByRaw("case estado when 'pendiente_validacion' then 0 when 'activo' then 1 else 2 end")->orderBy('nombre'),
        };

        $lista = $consulta->get();

        return view('admin.establecimientos', compact('lista', 'estado', 'tipo', 'rep', 'orden', 'q'));
    }

    private function reglasEst(): array
    {
        return [
            'tipo' => 'required|in:farmacia,centro_salud',
            'tipo_centro' => 'nullable|required_if:tipo,centro_salud|in:hospital,clinica,centro_de_salud',
            'nombre' => 'required|string|max:150',
            'direccion' => 'required|string|max:255',
            'telefono' => ['nullable', 'regex:/^[0-9]{7,8}$/'],
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'representante_id' => ['nullable', Rule::exists('users', 'id')->where('rol', 'representante')],
            'estado' => 'required|in:activo,inactivo,pendiente_validacion',
        ];
    }

    private function mensajesEst(): array
    {
        return [
            'telefono.regex' => 'El teléfono debe tener solo números (7 u 8 dígitos).',
            'latitud.numeric' => 'La latitud debe ser un número.',
            'longitud.numeric' => 'La longitud debe ser un número.',
            'tipo_centro.required_if' => 'Selecciona el tipo de centro.',
            'representante_id.exists' => 'El representante seleccionado no es válido.',
        ];
    }

    private function limpiarEst(Request $request): array
    {
        $request->merge(['telefono' => preg_replace('/\D/', '', (string) $request->input('telefono', ''))]);
        $datos = $request->validate($this->reglasEst(), $this->mensajesEst());

        $datos['telefono'] = ($datos['telefono'] ?? '') !== '' ? $datos['telefono'] : null;
        $datos['representante_id'] = $datos['representante_id'] ?? null;
        if ($datos['tipo'] === 'farmacia') {
            $datos['tipo_centro'] = null;
        }

        return $datos;
    }

    private function representantes()
    {
        return User::where('rol', 'representante')->where('activo', true)->orderBy('name')->get();
    }

    public function establecimientoCreate()
    {
        $this->soloAdmin();

        return view('admin.establecimiento_form', ['representantes' => $this->representantes()]);
    }

    public function establecimientoStore(Request $request): RedirectResponse
    {
        $this->soloAdmin();
        Establecimiento::create($this->limpiarEst($request));

        return redirect()->route('admin.establecimientos')->with('ok', 'Establecimiento registrado.');
    }

    public function establecimientoEdit(Establecimiento $establecimiento)
    {
        $this->soloAdmin();
        $establecimiento->load('fotos');

        return view('admin.establecimiento_form', [
            'e' => $establecimiento,
            'representantes' => $this->representantes(),
        ]);
    }

    public function establecimientoUpdate(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->soloAdmin();
        $establecimiento->update($this->limpiarEst($request));

        return redirect()->route('admin.establecimientos')->with('ok', 'Establecimiento actualizado.');
    }

    public function establecimientoDestroy(Establecimiento $establecimiento): RedirectResponse
    {
        $this->soloAdmin();
        $nombre = $establecimiento->nombre;
        $establecimiento->delete();

        return back()->with('ok', "«$nombre» fue eliminado.");
    }

    public function cambiarEstado(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->soloAdmin();
        $v = $request->validate(['estado' => 'required|in:activo,inactivo,pendiente_validacion']);
        $establecimiento->update(['estado' => $v['estado']]);

        return back()->with('ok', "«{$establecimiento->nombre}» ahora está: " . str_replace('_', ' ', $v['estado']) . '.');
    }

    // ---------- Reportes ----------
    public function reportes(Request $request)
    {
        $this->soloAdmin();
        $estado = $request->input('estado', 'pendiente');
        $orden = $request->input('orden', 'reciente');

        $lista = Reporte::with(['establecimiento', 'usuario'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->get();

        $lista = match ($orden) {
            'antiguo' => $lista->sortBy('created_at'),
            'nombre_asc' => $lista->sortBy(fn ($r) => mb_strtolower($r->establecimiento->nombre)),
            'nombre_desc' => $lista->sortByDesc(fn ($r) => mb_strtolower($r->establecimiento->nombre)),
            default => $lista->sortByDesc('created_at'),
        };

        return view('admin.reportes', ['lista' => $lista->values(), 'estado' => $estado, 'orden' => $orden]);
    }

    public function resolverReporte(Reporte $reporte): RedirectResponse
    {
        $this->soloAdmin();
        $reporte->update(['estado' => 'resuelto', 'administrador_id' => auth()->id()]);

        return back()->with('ok', 'Reporte marcado como resuelto.');
    }

    // ---------- Usuarios ----------
    public function usuarios(Request $request)
    {
        $this->soloAdmin();
        $rol = $request->input('rol');
        $activo = $request->input('activo');
        $orden = $request->input('orden', 'nombre_asc');
        $q = trim((string) $request->input('q', ''));

        $consulta = User::when($rol, fn ($x) => $x->where('rol', $rol))
            ->when($activo === 'si', fn ($x) => $x->where('activo', true))
            ->when($activo === 'no', fn ($x) => $x->where('activo', false))
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w->where('name', 'ilike', '%' . $q . '%')
                ->orWhere('email', 'ilike', '%' . $q . '%')));

        match ($orden) {
            'nombre_desc' => $consulta->orderByDesc('name'),
            'reciente' => $consulta->orderByDesc('created_at'),
            'antiguo' => $consulta->orderBy('created_at'),
            'rol' => $consulta->orderBy('rol')->orderBy('name'),
            'estado' => $consulta->orderByDesc('activo')->orderBy('name'),
            default => $consulta->orderBy('name'),
        };

        $lista = $consulta->get();

        // Establecimientos asignados a cada representante
        $asignados = Establecimiento::whereNotNull('representante_id')->orderBy('nombre')
            ->get(['id', 'nombre', 'representante_id'])->groupBy('representante_id');

        if ($orden === 'estab_desc') {
            $lista = $lista->sortByDesc(fn ($u) => $asignados->get($u->id)?->count() ?? 0)->values();
        } elseif ($orden === 'estab_asc') {
            $lista = $lista->sortBy(fn ($u) => $asignados->get($u->id)?->count() ?? 0)->values();
        }

        return view('admin.usuarios', compact('lista', 'rol', 'activo', 'orden', 'q', 'asignados'));
    }

    // El administrador crea cuentas de representante (o de otro administrador)
    public function usuarioStore(Request $request): RedirectResponse
    {
        $this->soloAdmin();
        $v = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:representante,administrador',
        ], [
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        User::forceCreate([
            'name' => $v['name'],
            'email' => $v['email'],
            'password' => Hash::make($v['password']),
            'rol' => $v['rol'],
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        return back()->with('ok', 'Cuenta de ' . $v['rol'] . ' creada: ' . $v['email']);
    }

    public function usuarioEstado(User $user): RedirectResponse
    {
        $this->soloAdmin();
        abort_if($user->id === auth()->id(), 403, 'No puedes desactivar tu propia cuenta.');

        $user->forceFill(['activo' => !$user->activo])->save();

        return back()->with('ok', $user->name . ($user->activo ? ' fue activado.' : ' fue desactivado.'));
    }

    // Editar perfil de un usuario (representante, administrador o ciudadano) y sus establecimientos
    public function usuarioEdit(User $user)
    {
        $this->soloAdmin();
        $todos = Establecimiento::with('representante')->orderBy('nombre')->get();

        return view('admin.usuario_edit', ['u' => $user, 'todos' => $todos]);
    }

    public function usuarioUpdate(Request $request, User $user): RedirectResponse
    {
        $this->soloAdmin();
        $v = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'rol' => 'required|in:ciudadano,representante,administrador',
            'password' => 'nullable|string|min:8|confirmed',
            'establecimientos' => 'nullable|array',
            'establecimientos.*' => 'exists:establecimientos,id',
        ], [
            'email.unique' => 'Ya existe otra cuenta con ese correo.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($user->id === auth()->id() && $v['rol'] !== $user->rol) {
            return back()->withErrors(['rol' => 'No puedes cambiar tu propio rol.'])->withInput();
        }

        $user->forceFill(['name' => $v['name'], 'email' => $v['email'], 'rol' => $v['rol']]);
        if (!empty($v['password'])) {
            $user->password = Hash::make($v['password']);
        }
        $user->save();

        if ($v['rol'] === 'representante') {
            $ids = $v['establecimientos'] ?? [];
            Establecimiento::where('representante_id', $user->id)->whereNotIn('id', $ids)->update(['representante_id' => null]);
            if ($ids) {
                Establecimiento::whereIn('id', $ids)->update(['representante_id' => $user->id]);
            }
        } else {
            Establecimiento::where('representante_id', $user->id)->update(['representante_id' => null]);
        }

        return redirect()->route('admin.usuarios')->with('ok', 'Perfil de ' . $user->name . ' actualizado.');
    }

    public function usuarioDestroy(User $user): RedirectResponse
    {
        $this->soloAdmin();
        abort_if($user->id === auth()->id(), 403, 'No puedes eliminar tu propia cuenta.');

        $nombre = $user->name;
        $user->delete(); // sus establecimientos quedan "sin asignar"

        return redirect()->route('admin.usuarios')->with('ok', "La cuenta de $nombre fue eliminada.");
    }
}
