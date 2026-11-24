<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La empresa real del caso. El sistema trabaja con UNA sola empresa
     * (la guía dice: un equipo, un caso, una empresa).
     */
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);                       // razon social
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('nit', 20)->nullable();
            $table->string('nrc', 20)->nullable();               // registro de IVA
            $table->string('giro');                              // actividad economica
            $table->string('clasificacion', 20);                 // enum ClasificacionEmpresa
            $table->string('tamano', 20);                        // enum TamanoEmpresa
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->decimal('tasa_iva', 5, 2)->default(13);      // % de IVA vigente
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};