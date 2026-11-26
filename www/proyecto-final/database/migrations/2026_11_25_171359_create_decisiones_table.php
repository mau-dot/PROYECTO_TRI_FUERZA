
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 6 (Economía). Crea 2 tablas:
 * decisiones: decisión de inversión del caso.
 * alternativas: opciones de inversión que se comparan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisiones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('tmar', 7, 4);
            $table->decimal('tasa_isr', 5, 2);
            $table->boolean('provisional')->default(false);
            $table->timestamps();
        });

        Schema::create('alternativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')
                ->constrained('decisiones')
                ->cascadeOnDelete();

            $table->foreignId('costeo_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('inversion', 15, 2);
            $table->unsignedSmallInteger('vida_util');
            $table->decimal('valor_salvamento', 15, 2)->default(0);
            $table->decimal('beneficio_anual', 15, 2)->default(0);
            $table->decimal('costo_operacion', 15, 2)->nullable();
            $table->boolean('provisional')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternativas');
        Schema::dropIfExists('decisiones');
    }
};
