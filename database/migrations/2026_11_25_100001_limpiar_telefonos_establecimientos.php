<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Deja los teléfonos existentes solo con números (quita guiones, espacios y letras)
    public function up(): void
    {
        DB::statement("UPDATE establecimientos SET telefono = NULLIF(regexp_replace(telefono, '[^0-9]', '', 'g'), '') WHERE telefono IS NOT NULL");
    }

    public function down(): void
    {
        // no reversible
    }
};
