<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lineas del asiento. Cada linea va al DEBE o al HABER, nunca a los dos.
     * Montos en decimal(15,2): nada de float en contabilidad.
     */
    public function up(): void
    {
        Schema::create('detalle_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cuenta_id')->constrained()->restrictOnDelete();
            $table->string('concepto')->nullable();
            $table->decimal('debe', 15, 2)->default(0);
            $table->decimal('haber', 15, 2)->default(0);
            $table->timestamps();
        });

        // Segunda barrera (ademas de la validacion del formulario), directo en la database
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE detalle_partidas ADD CONSTRAINT detalle_debe_o_haber
                CHECK ((debe > 0 AND haber = 0) OR (haber > 0 AND debe = 0))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_partidas');
    }
};