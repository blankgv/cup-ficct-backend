<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Asociación muchos-a-muchos: materias que cursa un grupo.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_materia', function (Blueprint $table) {
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->string('materia_sigla');
            $table->timestamps();

            // Clave primaria compuesta.
            $table->primary(['grupo_id', 'materia_sigla']);

            // FK a materia; renombrar/eliminar la materia cascadea.
            $table->foreign('materia_sigla')
                ->references('sigla')->on('materias')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_materia');
    }
};
