<?php

namespace App\Models;

use App\Models\Concerns\TieneEvidencias;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** La decisión de inversión del caso. Hay una sola. */
#[Fillable(['titulo', 'descripcion', 'tmar', 'tasa_isr', 'provisional'])]
class Decision extends Model
{
    use TieneEvidencias;

    protected $table = 'decisiones';

    protected $attributes = [
        'provisional' => false,
    ];

    protected function casts(): array
    {
        return [
            'tmar' => 'decimal:4',
            'tasa_isr' => 'decimal:2',
            'provisional' => 'boolean',
        ];
    }

    /** Obtiene la decisión actual del caso. */
    public static function actual(): ?self
    {
        return static::query()->oldest('id')->first();
    }

    public function alternativas(): HasMany
    {
        return $this->hasMany(Alternativa::class)->orderBy('nombre');
    }

    /** Convierte la TMAR de porcentaje a decimal. */
    public function i(): float
    {
        return (float) $this->tmar / 100;
    }

    /** Convierte el ISR de porcentaje a decimal. */
    public function t(): float
    {
        return (float) $this->tasa_isr / 100;
    }
}
