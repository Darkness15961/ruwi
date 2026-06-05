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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->string('url', 50);
            $table->string('icon', 50)->nullable();
            $table->unsignedBigInteger('menus_id')->nullable();
            $table->integer('orden')->default(0);
            $table->integer('activo')->default(1); // 1 = Activo, 0 = Inactivo
            $table->timestamps();

            $table->foreign('menus_id')->references('id')->on('menus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
