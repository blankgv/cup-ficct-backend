<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Convocatorias (periodos de admisión).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('gestion');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->default('ABIERTA');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatorias');
    }
};
