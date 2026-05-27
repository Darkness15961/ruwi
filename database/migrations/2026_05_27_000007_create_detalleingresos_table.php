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
        Schema::create('detalleingresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingresos_id')->constrained('ingresos')->onDelete('cascade');
            $table->foreignId('insumos_id')->constrained('insumos')->onDelete('cascade');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('punitario', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalleingresos');
    }
};
