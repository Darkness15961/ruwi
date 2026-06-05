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
        Schema::create('menuusuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menus_id');
            $table->unsignedBigInteger('empresausuario_id');
            $table->timestamps();

            $table->foreign('menus_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('empresausuario_id')->references('id')->on('empresausuarios')->onDelete('cascade');

            // Evitar duplicaciones de asignación
            $table->unique(['menus_id', 'empresausuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menuusuario');
    }
};
