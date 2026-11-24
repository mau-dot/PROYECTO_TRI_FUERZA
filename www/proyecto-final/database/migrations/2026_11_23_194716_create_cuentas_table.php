<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalogo de cuentas jerarquico.
     * El padre se calcula a partir del codigo: 1 > 11 > 1101 > 110101.
     * Solo las cuentas SIN subcuentas (de detalle) reciben movimientos.
     */
    public function up(): void
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->foreignId('padre_id')->nullable()->constrained('cuentas')->restrictOnDelete();
            $table->unsignedSmallInteger('nivel')->default(1);
            $table->string('tipo', 20);              // enum TipoCuenta
            $table->string('naturaleza', 20);        // enum Naturaleza
            $table->string('rubro', 20)->nullable(); // enum Rubro (activos y pasivos)
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};