<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Postulaciones. PK compuesta (postulante_documento, convocatoria_id).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->string('postulante_documento');
            $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
            $table->string('carrera_primera_codigo');
            $table->string('carrera_segunda_codigo');
            $table->string('estado')->default('PENDIENTE');
            $table->timestamps();

            $table->primary(['postulante_documento', 'convocatoria_id']);

            $table->foreign('postulante_documento')
                ->references('documento')->on('postulantes')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('carrera_primera_codigo')
                ->references('codigo')->on('carreras')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('carrera_segunda_codigo')
                ->references('codigo')->on('carreras')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};
