<?php

namespace App\Models;

use App\Enums\EstadoPeriodo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nombre', 'fecha_inicio', 'fecha_fin'])]
class Periodo extends Model
{
    /** Clave de sesion donde se guarda el periodo con el que trabaja el usuario */
    public const SESION = 'periodo_id';

    protected $attributes = [
        'estado' => 'abierto',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'estado' => EstadoPeriodo::class,
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function estaAbierto(): bool
    {
        return $this->estado === EstadoPeriodo::Abierto;
    }

    /**
     * Periodo activo: el que el usuario eligio (sesion); si no eligio ninguno,
     * el periodo abierto mas reciente; y si todos estan cerrados, el mas reciente.
     */
    public static function activo(): ?self
    {
        $id = session(self::SESION);

        if ($id && ($periodo = static::find($id))) {
            return $periodo;
        }

        return static::query()->where('estado', EstadoPeriodo::Abierto)->latest('fecha_inicio')->first()
            ?? static::query()->latest('fecha_inicio')->first();
    }

    /** "01/01/2026 al 30/06/2026" */
    public function rango(): string
    {
        return $this->fecha_inicio->format('d/m/Y').' al '.$this->fecha_fin->format('d/m/Y');
    }
}