<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Materias del curso.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            // La sigla es la clave primaria (no incremental).
            $table->string('sigla')->primary();
            $table->string('nombre');
            // Ponderación de importancia (0.0000 a 1.0000).
            $table->decimal('peso', 5, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
