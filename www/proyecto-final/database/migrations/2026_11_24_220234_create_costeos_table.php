<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 *   sistema de costos. Crea 2 tablas:
 *   costeos           -> un costeo por alternativa de inversion
 *   costeo_elementos  -> sus costos (MPD, MOD, CIF, gastos), cada uno fijo o variable
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costeos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);                         // name
            $table->text('descripcion')->nullable();
            $table->string('sistema', 20)->default('estandar');    // enum SistemaCosteo
            $table->string('unidad', 60);                          // unidad de costeo: "metro lineal de exhibicion"
            $table->decimal('volumen_anual', 12, 2);               // cuantas unidades al año
            $table->boolean('provisional')->default(false);        // datos supuestos, a reemplazar
            $table->timestamps();
        });

        Schema::create('costeo_elementos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('costeo_id')->constrained()->cascadeOnDelete();
            $table->string('elemento', 20);                        // enum ElementoCosto
            $table->string('comportamiento', 10);                  // enum Comportamiento
            $table->string('concepto', 150);
            $table->decimal('cantidad', 12, 2);                    // cantidad ANUAL (horas, unidades, meses...)
            $table->string('unidad_medida', 30)->nullable();       // "horas", "meses", "unidades"
            $table->decimal('costo_unitario', 15, 2);              // precio por cada unidad de medida
            $table->foreignId('cuenta_id')->nullable()->constrained()->nullOnDelete(); // de q cuenta del mayor sale
            $table->boolean('estimado')->default(false);           // true = supuesto, falta respaldarlo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costeo_elementos');
        Schema::dropIfExists('costeos');
    }
};