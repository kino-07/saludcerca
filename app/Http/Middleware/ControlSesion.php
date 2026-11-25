<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Seguridad de sesión:
 *  - Cierra la sesión tras 15 minutos de inactividad.
 *  - Bloquea las cuentas desactivadas por el administrador.
 */
class ControlSesion
{
    public const MINUTOS_INACTIVIDAD = 15;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (isset($user->activo) && !$user->activo) {
                return $this->cerrar($request, 'Tu cuenta está desactivada. Comunícate con el administrador.');
            }

            $ultima = $request->session()->get('ultima_actividad');
            if ($ultima && (now()->timestamp - $ultima) >= self::MINUTOS_INACTIVIDAD * 60) {
                return $this->cerrar($request, 'Tu sesión se cerró por inactividad (' . self::MINUTOS_INACTIVIDAD . ' minutos). Ingresa de nuevo.');
            }

            $request->session()->put('ultima_actividad', now()->timestamp);
        }

        return $next($request);
    }

    private function cerrar(Request $request, string $mensaje)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $mensaje]);
    }
}
