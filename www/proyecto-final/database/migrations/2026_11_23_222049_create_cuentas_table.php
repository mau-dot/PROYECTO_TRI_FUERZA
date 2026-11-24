<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evidencias: los documentos que respaldan los datos (facturas, cotizaciones, entrevistas...).
     * El archivo se guarda en storage/app/private/evidencias (no es publico: se abre con sesion).
     */
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 30);                          // enum TipoEvidencia
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();             // en entrevistas: el resumen de lo que se dijo
            $table->date('fecha')->nullable();                   // fecha del documento o de la entrevista
            $table->string('fuente', 150)->nullable();           // proveedor, persona entrevistada, institución
            $table->string('cargo', 100)->nullable();            // cargo del entrevistado
            $table->string('archivo_ruta')->nullable();          // ruta dentro del disco "local"
            $table->string('archivo_nombre')->nullable();        // nombre original del archivo
            $table->string('archivo_mime', 100)->nullable();
            $table->unsignedInteger('archivo_tamano')->nullable(); // en bytes
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        /*
         * Vínculos: qué dato respalda cada evidencia (relación polimorfica).
         * evidenciable_type + evidenciable_id dicen el registro ("partida" 5, "empresa" 1...)
         * y "campo" dice qué dato de ese registro ('' = el registro completo).
         */
        Schema::create('evidenciables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidencia_id')->constrained('evidencias')->cascadeOnDelete();
            $table->morphs('evidenciable');                      // evidenciable_type y evidenciable_id
            $table->string('campo', 50)->default('');
            $table->timestamps();

            $table->unique(['evidencia_id', 'evidenciable_type', 'evidenciable_id', 'campo'], 'evidenciables_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidenciables');
        Schema::dropIfExists('evidencias');
    }
};