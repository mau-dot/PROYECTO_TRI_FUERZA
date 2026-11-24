<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Una fila de "evidenciables": la evidencia X respalda el campo Y del registro Z.
 */
#[Fillable(['evidencia_id', 'evidenciable_type', 'evidenciable_id', 'campo'])]
class Vinculo extends Model
{
    protected $table = 'evidenciables';

    public function evidencia(): BelongsTo
    {
        return $this->belongsTo(Evidencia::class);
    }

    /** El registro respaldado (una Partida, la Empresa, una Cuenta...) */
    public function evidenciable(): MorphTo
    {
        return $this->morphTo();
    }

    /** "Partida #3", "Empresa", "Cuenta 110101 - Caja general" */
    public function descripcionDato(): string
    {
        $registro = $this->evidenciable;
        $tipo = config("evidencias.vinculables.{$this->evidenciable_type}.nombre", $this->evidenciable_type);

        $texto = match (true) {
            $registro instanceof Partida => "Partida #{$registro->numero} ({$registro->periodo?->nombre})",
            $registro instanceof Cuenta => "Cuenta {$registro->etiqueta()}",
            $registro instanceof Empresa => 'Empresa: '.($registro->nombre_comercial ?: $registro->nombre),
            $registro === null => "$tipo (eliminado)",
            default => $tipo.' #'.$this->evidenciable_id,
        };

        $campo = config("evidencias.vinculables.{$this->evidenciable_type}.campos.{$this->campo}");

        return $this->campo !== '' && $campo ? "$texto · $campo" : $texto;
    }

    /** Enlace a la pantalla del dato respaldado */
    public function urlDato(): ?string
    {
        return match (true) {
            $this->evidenciable instanceof Partida => route('contabilidad.partidas.show', $this->evidenciable),
            $this->evidenciable instanceof Empresa => route('contabilidad.empresa.edit'),
            $this->evidenciable instanceof Cuenta => route('contabilidad.cuentas.edit', $this->evidenciable),
            default => null,
        };
    }
}