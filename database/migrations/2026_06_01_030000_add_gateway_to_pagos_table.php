<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pasarela y referencia externa del pago (Stripe, PayPal, etc.).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('estado');
            $table->string('referencia')->nullable()->after('gateway');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'referencia']);
        });
    }
};
