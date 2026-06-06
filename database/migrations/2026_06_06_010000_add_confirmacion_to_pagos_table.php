<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Auditoría de confirmación/rechazo manual del pago.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('confirmado_por')->nullable()->after('estado')->constrained('users')->nullOnDelete();
            $table->dateTime('confirmado_at')->nullable()->after('confirmado_por');
            $table->string('motivo_rechazo')->nullable()->after('confirmado_at');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmado_por');
            $table->dropColumn(['confirmado_at', 'motivo_rechazo']);
        });
    }
};
