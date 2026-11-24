<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    protected $table = 'reportes';

    protected $fillable = ['user_id', 'establecimiento_id', 'descripcion', 'estado', 'administrador_id'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }
}
