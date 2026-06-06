<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Periodo de clases (temporada): rango de fechas en que se dicta el curso.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('gestion');
            $table->date('fecha_inicio_clases');
            $table->date('fecha_fin_clases');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
