<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Aulas. PK compuesta (modulo_numero, numero).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->string('modulo_numero');
            $table->unsignedInteger('numero');
            $table->string('nombre');
            $table->unsignedInteger('capacidad');
            $table->unsignedInteger('piso');
            $table->string('tipo');
            $table->timestamps();

            // Clave primaria compuesta.
            $table->primary(['modulo_numero', 'numero']);

            // FK al módulo; renombrar/eliminar el módulo cascadea.
            $table->foreign('modulo_numero')
                ->references('numero')->on('modulos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
