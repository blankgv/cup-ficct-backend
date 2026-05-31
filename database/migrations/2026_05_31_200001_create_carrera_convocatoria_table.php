<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cupos por carrera en cada convocatoria. PK compuesta.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrera_convocatoria', function (Blueprint $table) {
            $table->string('carrera_codigo');
            $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
            $table->unsignedInteger('cupos');
            $table->timestamps();

            $table->primary(['carrera_codigo', 'convocatoria_id']);

            $table->foreign('carrera_codigo')
                ->references('codigo')->on('carreras')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrera_convocatoria');
    }
};
