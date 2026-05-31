<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Docente que dicta la materia a ese grupo.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupo_materia', function (Blueprint $table) {
            $table->string('docente_ci')->nullable()->after('materia_sigla');

            $table->foreign('docente_ci')
                ->references('ci')->on('docentes')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grupo_materia', function (Blueprint $table) {
            $table->dropConstrainedForeignId('docente_ci');
        });
    }
};
