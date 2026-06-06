<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pagos de la postulación (inscripción al CUP).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->string('postulante_documento');
            $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
            $table->decimal('monto', 10, 2);
            $table->string('concepto');
            $table->string('metodo');
            $table->dateTime('fecha_pago');
            $table->string('estado')->default('PENDIENTE');
            $table->timestamps();

            // El pago pertenece a una postulación existente (FK compuesta).
            $table->foreign(['postulante_documento', 'convocatoria_id'])
                ->references(['postulante_documento', 'convocatoria_id'])->on('postulaciones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
