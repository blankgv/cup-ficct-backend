<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Horarios. PK compuesta (grupo_id, materia_sigla, numero).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->unsignedBigInteger('grupo_id');
            $table->string('materia_sigla');
            $table->unsignedInteger('numero');
            $table->string('dia');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('aula_modulo_numero');
            $table->unsignedInteger('aula_numero');
            $table->timestamps();

            // PK compuesta.
            $table->primary(['grupo_id', 'materia_sigla', 'numero']);

            // El horario pertenece a una asociación grupo-materia.
            $table->foreign(['grupo_id', 'materia_sigla'])
                ->references(['grupo_id', 'materia_sigla'])->on('grupo_materia')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // El horario ocurre en un aula (FK compuesta).
            $table->foreign(['aula_modulo_numero', 'aula_numero'])
                ->references(['modulo_numero', 'numero'])->on('aulas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
