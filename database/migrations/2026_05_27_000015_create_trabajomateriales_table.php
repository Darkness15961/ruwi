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
        Schema::create('trabajomateriales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajos_id')->constrained('trabajos')->onDelete('cascade');
            $table->foreignId('materiales_id')->constrained('materiales')->onDelete('cascade');
            $table->decimal('cantidad_usada', 10, 2);
            $table->decimal('costo', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajomateriales');
    }
};
