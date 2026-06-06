<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Inscripción del postulante a un grupo (resultado del flujo postula→paga→inscribe).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->string('postulante_documento');
            $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->dateTime('fecha_asignacion');
            $table->timestamps();

            // Un postulante = una inscripción por convocatoria.
            $table->primary(['postulante_documento', 'convocatoria_id']);

            // La inscripción pertenece a una postulación existente (FK compuesta).
            $table->foreign(['postulante_documento', 'convocatoria_id'])
                ->references(['postulante_documento', 'convocatoria_id'])->on('postulaciones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
