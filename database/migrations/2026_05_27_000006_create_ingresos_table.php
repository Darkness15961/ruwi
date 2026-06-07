<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('detalle');
            $table->string('origen'); // Boleta, Factura, Nota de crédito, Nota de débito
            $table->string('ruc_factura')->nullable();
            $table->string('serie_factura')->nullable();
            $table->integer('nro_factura')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
