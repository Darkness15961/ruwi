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
        Schema::create('cotizacions', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('ruc');
            $table->string('descripcion');
            $table->text('detalle')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('condicion')->nullable();
            $table->integer('users_id');
            $table->string('foto_ref')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacions');
    }
};
