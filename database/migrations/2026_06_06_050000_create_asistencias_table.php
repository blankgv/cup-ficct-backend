<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Asistencia diaria: postulante × convocatoria × materia × fecha.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->string('postulante_documento');
            $table->foreignId('convocatoria_id');
            $table->string('materia_sigla');
            $table->date('fecha');
            $table->string('estado');
            $table->timestamps();

            // Una asistencia por postulante/convocatoria/materia/día.
            $table->primary(['postulante_documento', 'convocatoria_id', 'materia_sigla', 'fecha']);

            // La asistencia pertenece a una inscripción existente (FK compuesta).
            $table->foreign(['postulante_documento', 'convocatoria_id'])
                ->references(['postulante_documento', 'convocatoria_id'])->on('inscripciones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('materia_sigla')->references('sigla')->on('materias')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
