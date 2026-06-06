<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Carrera definitiva asignada al inscrito (resultado del CUP).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->string('carrera_asignada_codigo')->nullable()->after('grupo_id');

            $table->foreign('carrera_asignada_codigo')->references('codigo')->on('carreras')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropForeign(['carrera_asignada_codigo']);
            $table->dropColumn('carrera_asignada_codigo');
        });
    }
};
