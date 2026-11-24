<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('representante_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo');                    // farmacia | centro_salud
            $table->string('tipo_centro')->nullable(); // centro_de_salud | hospital | clinica
            $table->string('nombre');
            $table->string('direccion');
            $table->string('telefono')->nullable();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->string('estado')->default('pendiente_validacion'); // activo | inactivo | pendiente_validacion
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establecimientos');
    }
};
