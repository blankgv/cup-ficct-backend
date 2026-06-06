<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Vincula el grupo a su periodo de clases (null = sin calendario definido).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->foreignId('periodo_id')->nullable()->after('convocatoria_id')->constrained('periodos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_id');
        });
    }
};
