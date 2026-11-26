<?php

namespace App\Models;

use App\Models\Concerns\TieneEvidencias;
use App\Support\Dinero;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Alternativa de inversión vinculada al sistema de costos. */
#[Fillable([
    'costeo_id',
    'nombre',
    'descripcion',
    'inversion',
    'vida_util',
    'valor_salvamento',
    'beneficio_anual',
    'costo_operacion',
    'provisional'
])]
class Alternativa extends Model
{
    use TieneEvidencias;

    protected $attributes = [
        'provisional' => false,
    ];

    protected function casts(): array
    {
        return [
            'inversion' => 'decimal:2',
            'vida_util' => 'integer',
            'valor_salvamento' => 'decimal:2',
            'beneficio_anual' => 'decimal:2',
            'costo_operacion' => 'decimal:2',
            'provisional' => 'boolean',
        ];
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(Decision::class);
    }

    public function costeo(): BelongsTo
    {
        return $this->belongsTo(Costeo::class);
    }

    /** Obtiene el costo anual de operación. */
    public function costoOperacion(): float
    {
        if ($this->costeo) {
            return $this->costeo->resumen()['total'] / 100;
        }

        return (float) $this->costo_operacion;
    }

    /** Identifica el origen del costo. */
    public function origenCosto(): string
    {
        return $this->costeo
            ? 'Sistema de costos'
            : 'Ingreso manual';
    }

    /** Calcula el flujo anual antes de impuestos. */
    public function flujoAnual(): float
    {
        return round(
            (float) $this->beneficio_anual - $this->costoOperacion(),
            2
        );
    }

    public function formato(string $campo): string
    {
        return Dinero::formato(Dinero::centavos($this->{$campo}));
    }
}
