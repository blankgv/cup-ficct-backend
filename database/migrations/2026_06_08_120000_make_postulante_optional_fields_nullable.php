<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Permite registro público con datos mínimos: el postulante completa estos campos luego.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->change();
            $table->string('colegio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable(false)->change();
            $table->string('colegio')->nullable(false)->change();
        });
    }
};
