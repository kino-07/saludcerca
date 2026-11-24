<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Agregación: el servicio existe aunque se elimine un establecimiento
        Schema::create('establecimiento_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->unique(['establecimiento_id', 'servicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establecimiento_servicio');
        Schema::dropIfExists('servicios');
    }
};
