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
        Schema::create('trabajoarchivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajos_id')->constrained('trabajos')->onDelete('cascade');
            $table->string('tipo'); // STL, GCODE, OBJ
            $table->string('link');
            $table->string('captura')->nullable();
            $table->decimal('peso_mb', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajoarchivos');
    }
};
