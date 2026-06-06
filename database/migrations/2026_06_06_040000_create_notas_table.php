<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Notas por examen: postulante × convocatoria × materia × número de examen.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->string('postulante_documento');
            $table->foreignId('convocatoria_id');
            $table->string('materia_sigla');
            $table->unsignedSmallInteger('numero');
            $table->decimal('valor', 5, 2);
            $table->timestamps();

            // Una nota por postulante/convocatoria/materia/examen.
            $table->primary(['postulante_documento', 'convocatoria_id', 'materia_sigla', 'numero']);

            // La nota pertenece a una inscripción existente (FK compuesta).
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
        Schema::dropIfExists('notas');
    }
};
