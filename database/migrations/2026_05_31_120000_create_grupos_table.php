<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Grupos (paralelos/cohortes).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->string('turno');
            $table->unsignedSmallInteger('capacidad');
            $table->string('gestion');
            $table->timestamps();

            // El código no se repite dentro de la misma gestión.
            $table->unique(['codigo', 'gestion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
