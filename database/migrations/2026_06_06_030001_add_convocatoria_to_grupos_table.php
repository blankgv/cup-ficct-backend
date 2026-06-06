<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Vincula un grupo autogenerado a su convocatoria (null = grupo creado a mano).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->foreignId('convocatoria_id')->nullable()->after('gestion')->constrained('convocatorias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('convocatoria_id');
        });
    }
};
