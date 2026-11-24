<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Establecimiento extends Model
{
    protected $table = 'establecimientos';

    protected $fillable = [
        'representante_id', 'tipo', 'tipo_centro', 'nombre', 'direccion',
        'telefono', 'latitud', 'longitud', 'estado',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function representante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representante_id');
    }

    // Composición: se eliminan junto con el establecimiento
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(Foto::class);
    }

    // Agregación: los servicios existen por sí solos
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'establecimiento_servicio');
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    // Distancia (km) con la fórmula de Haversine, ordenada de menor a mayor
    public function scopeCercanos(Builder $query, float $lat, float $lng): Builder
    {
        return $query->selectRaw(
            'establecimientos.*, (6371 * acos(least(1, cos(radians(?)) * cos(radians(latitud)) * cos(radians(longitud) - radians(?)) + sin(radians(?)) * sin(radians(latitud))))) as distancia_km',
            [$lat, $lng, $lat]
        )->orderBy('distancia_km');
    }

    public function estaDeTurno($fecha = null): bool
    {
        $fecha = $fecha ?: now('America/La_Paz');

        return $this->turnos->contains(fn (Turno $t) => $t->estaVigente($fecha));
    }

    public function estaAbiertoAhora(): bool
    {
        $ahora = now('America/La_Paz');
        $dias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
        $dia = $dias[$ahora->dayOfWeekIso];
        $hora = $ahora->format('H:i:s');

        return $this->horarios->contains(
            fn (Horario $h) => $h->dia_semana === $dia && $hora >= $h->hora_apertura && $hora <= $h->hora_cierre
        );
    }
}
