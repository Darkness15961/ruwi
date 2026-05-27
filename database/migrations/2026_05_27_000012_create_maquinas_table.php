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
        Schema::create('maquinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulos_id')->constrained('modulos')->onDelete('cascade');
            $table->string('nombre');
            $table->string('marca');
            $table->string('modelo');
            $table->string('serie');
            $table->decimal('potencia_watts', 10, 2);
            $table->string('estado'); // ACTIVA, MANTENIMIENTO, FUERA_SERVICIO
            $table->date('fecha_compra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinas');
    }
};
