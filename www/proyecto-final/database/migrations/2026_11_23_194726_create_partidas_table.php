<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Encabezado del asiento contable (libro diario).
     */
    public function up(): void
    {
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('numero');                 // correlativo dentro del periodo
            $table->date('fecha');
            $table->string('tipo', 20)->default('diario');     // enum TipoPartida
            $table->string('concepto', 500);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // quien la registro
            $table->timestamps();

            $table->unique(['periodo_id', 'numero']);
            $table->index(['periodo_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};