<?php

namespace App\Models;

use App\Enums\Comportamiento;
use App\Enums\ElementoCosto;
use App\Models\Concerns\TieneEvidencias;
use App\Services\Contabilidad\CalculoCosteo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*un costo de la alternativa: cantidad anual × costo unitario (p. ej. 208 horas × $2.15) */
#[Fillable(['elemento', 'comportamiento', 'concepto', 'cantidad', 'unidad_medida', 'costo_unitario', 'cuenta_id', 'estimado'])]
class CosteoElemento extends Model
{
    use TieneEvidencias;

    protected $attributes = [
        'estimado' => false,
    ];

    protected function casts(): array
    {
        return [
            'elemento' => ElementoCosto::class,
            'comportamiento' => Comportamiento::class,
            'cantidad' => 'decimal:2',
            'costo_unitario' => 'decimal:2',
            'estimado' => 'boolean',
        ];
    }

    public function costeo(): BelongsTo
    {
        return $this->belongsTo(Costeo::class);
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /*monto anual en centavos = cantidad × costo unitario */
    public function monto(): int
    {
        return CalculoCosteo::monto($this->cantidad, $this->costo_unitario);
    }
}