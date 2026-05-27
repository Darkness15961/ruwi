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
        // 1. Empresas
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ruc');
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('cci')->nullable();
            $table->timestamps();
        });

        // 2. Categorias
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        // 3. Modulos
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo_modulo');
            $table->timestamps();
        });

        // 4. Ingresos
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('origen');
            $table->string('ruc_factura');
            $table->string('serie_factura');
            $table->integer('nro_factura');
            $table->timestamps();
        });

        // 5. Cotizaciones
        Schema::create('cotizacion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->bigInteger('ruc');
            $table->string('descripcion');
            $table->text('detalle')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('condicion')->nullable();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('foto_ref')->nullable();
            $table->integer('estado');
            $table->timestamps();
        });

        // 6. Empresa Usuarios
        Schema::create('empresausuarios', function (Blueprint $table) {
            $table->id();
            $table->string('cargo');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('empresas_id')->constrained('empresas')->onDelete('cascade');
            $table->timestamps();
        });

        // 7. Cuentas
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresas_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nombre');
            $table->string('moneda');
            $table->string('nro_cuenta');
            $table->timestamps();
        });

        // 8. Insumos
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('umedida');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('insumo_id')->nullable()->constrained('insumos')->onDelete('set null');
            $table->timestamps();
        });

        // 9. Detalle Ingresos
        Schema::create('detalleingreso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingreso_id')->constrained('ingresos')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('punitario', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });

        // 10. Productos
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('nombre');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('punitario', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->foreignId('cotizacion_id')->constrained('cotizacion')->onDelete('cascade');
            $table->timestamps();
        });

        // 11. Producto Insumos
        Schema::create('productoinsumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalleingreso_id')->constrained('detalleingreso')->onDelete('cascade');
            $table->foreignId('productos_id')->constrained('productos')->onDelete('cascade');
            $table->decimal('cantidad', 10, 2);
            $table->string('destino');
            $table->integer('estado');
            $table->timestamps();
        });

        // 12. Maquinas
        Schema::create('maquinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->onDelete('cascade');
            $table->string('nombre');
            $table->string('marca');
            $table->string('modelo');
            $table->string('serie');
            $table->decimal('potencia_watts', 10, 2);
            $table->string('estado'); // ACTIVA, MANTENIMIENTO, FUERA_SERVICIO
            $table->date('fecha_compra')->nullable();
            $table->timestamps();
        });

        // 13. Materiales
        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo'); // FILAMENTO, RESINA
            $table->string('color')->nullable();
            $table->decimal('cantidad', 10, 2);
            $table->decimal('costo', 10, 2);
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->timestamps();
        });

        // 14. Trabajos
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maquina_id')->constrained('maquinas')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('estado'); // PENDIENTE, IMPRIMIENDO, FINALIZADO, FALLIDO
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->integer('tiempo_estimado_min');
            $table->integer('tiempo_real_min')->nullable();
            $table->decimal('costo_total', 10, 2)->nullable();
            $table->timestamps();
        });

        // 15. Trabajo Materiales
        Schema::create('trabajomateriales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajo_id')->constrained('trabajos')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materiales')->onDelete('cascade');
            $table->decimal('cantidad_usada', 10, 2);
            $table->decimal('costo', 10, 2);
            $table->timestamps();
        });

        // 16. Trabajo Energia
        Schema::create('trabajoenergia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajo_id')->constrained('trabajos')->onDelete('cascade');
            $table->decimal('kilowatts_hora', 10, 4);
            $table->decimal('costo_kwh', 10, 2);
            $table->decimal('costo_total', 10, 2);
            $table->timestamps();
        });

        // 17. Trabajo Archivos
        Schema::create('trabajoarchivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajo_id')->constrained('trabajos')->onDelete('cascade');
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
        // Drop in reverse order to respect foreign key constraints
        Schema::dropIfExists('trabajoarchivos');
        Schema::dropIfExists('trabajoenergia');
        Schema::dropIfExists('trabajomateriales');
        Schema::dropIfExists('trabajos');
        Schema::dropIfExists('materiales');
        Schema::dropIfExists('maquinas');
        Schema::dropIfExists('productoinsumos');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('detalleingreso');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('cuentas');
        Schema::dropIfExists('empresausuarios');
        Schema::dropIfExists('cotizacion');
        Schema::dropIfExists('ingresos');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('empresas');
    }
};
