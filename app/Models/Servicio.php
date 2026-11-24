<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servicio extends Model
{
    protected $fillable = ['nombre'];

    public function establecimientos(): BelongsToMany
    {
        return $this->belongsToMany(Establecimiento::class, 'establecimiento_servicio');
    }
}
