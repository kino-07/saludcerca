<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepresentanteController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('mapa'));

// Después del login, cada usuario va a su panel según su rol
Route::get('/dashboard', fn () => redirect()->route('panel'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---- SaludCerca: mapa y búsqueda ----
Route::get('/mapa', [\App\Http\Controllers\MapaController::class, 'index'])->name('mapa');
Route::get('/api/establecimientos', [\App\Http\Controllers\MapaController::class, 'buscar'])->name('api.establecimientos');

// ---- SaludCerca: ficha del establecimiento ----
Route::get('/establecimiento/{establecimiento}', [\App\Http\Controllers\FichaController::class, 'show'])->name('establecimiento.ficha');

// ---- SaludCerca: ciudadano (favoritos y reportes) ----
Route::middleware('auth')->group(function () {
    Route::get('/mi-espacio', [CiudadanoController::class, 'index'])->name('ciudadano.index');
    Route::post('/establecimiento/{establecimiento}/favorito', [CiudadanoController::class, 'toggleFavorito'])->name('favorito.toggle');
    Route::post('/establecimiento/{establecimiento}/reporte', [CiudadanoController::class, 'reportar'])->name('reporte.store');
});

// ---- SaludCerca: panel del representante ----
Route::middleware('auth')->group(function () {
    Route::get('/panel', [RepresentanteController::class, 'panel'])->name('panel');

    Route::prefix('representante')->name('representante.')->group(function () {
        $c = RepresentanteController::class;
        Route::get('/', [$c, 'index'])->name('index');
        Route::get('/nuevo', [$c, 'create'])->name('create');
        Route::post('/', [$c, 'store'])->name('store');
        Route::get('/{establecimiento}', [$c, 'gestionar'])->name('gestionar');
        Route::put('/{establecimiento}/datos', [$c, 'actualizarDatos'])->name('datos');
        Route::post('/{establecimiento}/horarios', [$c, 'guardarHorarios'])->name('horarios');
        Route::post('/{establecimiento}/turnos', [$c, 'turnoStore'])->name('turnos.store');
        Route::delete('/{establecimiento}/turnos/{turno}', [$c, 'turnoDestroy'])->name('turnos.destroy');
    });
});

// ---- SaludCerca: panel del administrador ----
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/establecimientos', [AdminController::class, 'establecimientos'])->name('establecimientos');
    Route::patch('/establecimientos/{establecimiento}/estado', [AdminController::class, 'cambiarEstado'])->name('estado');
    Route::get('/reportes', [AdminController::class, 'reportes'])->name('reportes');
    Route::patch('/reportes/{reporte}/resolver', [AdminController::class, 'resolverReporte'])->name('reportes.resolver');
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
});

require __DIR__.'/auth.php';
