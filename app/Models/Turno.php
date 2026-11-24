<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turno extends Model
{
    protected $table = 'turnos';

    protected $fillable = ['establecimiento_id', 'fecha_inicio', 'fecha_fin'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    // ¿La fecha cae dentro del periodo del turno?
    public function estaVigente($fecha = null): bool
    {
        $fecha = Carbon::parse($fecha ?: now('America/La_Paz'))->startOfDay();

        return $fecha->betweenIncluded($this->fecha_inicio->copy()->startOfDay(), $this->fecha_fin->copy()->startOfDay());
    }
}
