<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepresentanteController;
use App\Http\Middleware\ControlSesion;
use Illuminate\Support\Facades\Route;

// La primera pantalla siempre es el mapa
Route::get('/', fn () => redirect()->route('mapa'));
Route::get('/dashboard', fn () => redirect()->route('mapa'))->middleware(['auth', 'verified'])->name('dashboard');

// API del mapa (límite de peticiones por minuto para no saturar el sistema)
Route::get('/api/establecimientos', [MapaController::class, 'buscar'])
    ->middleware('throttle:120,1')->name('api.establecimientos');

// Todo lo demás pasa por el control de sesión (inactividad y cuentas desactivadas)
Route::middleware(ControlSesion::class)->group(function () {

    Route::middleware('throttle:120,1')->group(function () {
        Route::get('/mapa', [MapaController::class, 'index'])->name('mapa');
        Route::get('/establecimiento/{establecimiento}', [FichaController::class, 'show'])->name('establecimiento.ficha');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/panel', [RepresentanteController::class, 'panel'])->name('panel');

        // Ciudadano
        Route::get('/mi-espacio', [CiudadanoController::class, 'index'])->name('ciudadano.index');
        Route::post('/establecimiento/{establecimiento}/favorito', [CiudadanoController::class, 'toggleFavorito'])
            ->middleware('throttle:30,1')->name('favorito.toggle');
        Route::post('/establecimiento/{establecimiento}/reporte', [CiudadanoController::class, 'reportar'])
            ->middleware('throttle:5,1')->name('reporte.store');

        // Fotos (administrador o representante dueño)
        Route::post('/establecimiento/{establecimiento}/fotos', [FotoController::class, 'store'])
            ->middleware('throttle:20,1')->name('fotos.store');
        Route::delete('/fotos/{foto}', [FotoController::class, 'destroy'])->name('fotos.destroy');

        // Representante (solo gestiona lo que el administrador le asignó)
        Route::prefix('representante')->name('representante.')->group(function () {
            $c = RepresentanteController::class;
            Route::get('/', [$c, 'index'])->name('index');
            Route::get('/{establecimiento}', [$c, 'gestionar'])->name('gestionar');
            Route::put('/{establecimiento}/datos', [$c, 'actualizarDatos'])->name('datos');
            Route::post('/{establecimiento}/horarios', [$c, 'guardarHorarios'])->name('horarios');
            Route::post('/{establecimiento}/turnos', [$c, 'turnoStore'])->name('turnos.store');
            Route::delete('/{establecimiento}/turnos/{turno}', [$c, 'turnoDestroy'])->name('turnos.destroy');
        });

        // Administrador
        Route::prefix('admin')->name('admin.')->group(function () {
            $a = AdminController::class;
            Route::get('/', [$a, 'index'])->name('index');

            Route::get('/establecimientos', [$a, 'establecimientos'])->name('establecimientos');
            Route::get('/establecimientos/nuevo', [$a, 'establecimientoCreate'])->name('establecimientos.create');
            Route::post('/establecimientos', [$a, 'establecimientoStore'])->name('establecimientos.store');
            Route::get('/establecimientos/{establecimiento}/editar', [$a, 'establecimientoEdit'])->name('establecimientos.edit');
            Route::put('/establecimientos/{establecimiento}', [$a, 'establecimientoUpdate'])->name('establecimientos.update');
            Route::delete('/establecimientos/{establecimiento}', [$a, 'establecimientoDestroy'])->name('establecimientos.destroy');
            Route::patch('/establecimientos/{establecimiento}/estado', [$a, 'cambiarEstado'])->name('estado');

            Route::get('/reportes', [$a, 'reportes'])->name('reportes');
            Route::patch('/reportes/{reporte}/resolver', [$a, 'resolverReporte'])->name('reportes.resolver');

            Route::get('/usuarios', [$a, 'usuarios'])->name('usuarios');
            Route::post('/usuarios', [$a, 'usuarioStore'])->name('usuarios.store');
            Route::get('/usuarios/{user}/editar', [$a, 'usuarioEdit'])->name('usuarios.edit');
            Route::put('/usuarios/{user}', [$a, 'usuarioUpdate'])->name('usuarios.update');
            Route::delete('/usuarios/{user}', [$a, 'usuarioDestroy'])->name('usuarios.destroy');
            Route::patch('/usuarios/{user}/estado', [$a, 'usuarioEstado'])->name('usuarios.estado');
        });
    });
});

// Login, registro, etc. (Breeze) con límite de peticiones para evitar inundación
Route::middleware('throttle:30,1')->group(function () {
    require __DIR__.'/auth.php';
});
