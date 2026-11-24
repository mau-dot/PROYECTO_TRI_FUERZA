<?php

namespace App\Models\Concerns;

use App\Models\Evidencia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Se agrega a cualquier modelo cuyos datos necesiten respaldo (Partida, Empresa, Cuenta y,
 * mas adelante, las alternativas de economia):
 *
 *     use TieneEvidencias;
 *
 *     $partida->evidencias          todas sus evidencias
 *     $partida->fuentes()           las que respaldan el registro completo
 *     $empresa->fuentes('autorizacion')   las que respaldan un dato concreto
 */
trait TieneEvidencias
{
    /** Al borrar el registro se borran sus vinculos (las evidencias se quedan) */
    public static function bootTieneEvidencias(): void
    {
        static::deleting(fn ($modelo) => $modelo->evidencias()->detach());
    }

    public function evidencias(): MorphToMany
    {
        return $this->morphToMany(Evidencia::class, 'evidenciable', 'evidenciables')
            ->withPivot(['id', 'campo'])
            ->withTimestamps();
    }

    /** Evidencias que respaldan un campo ('' = el registro completo) */
    public function fuentes(string $campo = ''): Collection
    {
        return $this->evidencias->where('pivot.campo', $campo)->values();
    }

    public function numFuentes(string $campo = ''): int
    {
        return $this->fuentes($campo)->count();
    }
}