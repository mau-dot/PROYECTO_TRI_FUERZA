<?php

namespace App\Models;

use App\Models\Concerns\TieneEvidencias;

use App\Enums\Naturaleza;
use App\Enums\Rubro;
use App\Enums\TipoCuenta;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['codigo', 'nombre', 'tipo', 'naturaleza', 'rubro', 'activa'])]
class Cuenta extends Model
{
    use TieneEvidencias;

    protected $attributes = [
        'activa' => true,
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCuenta::class,
            'naturaleza' => Naturaleza::class,
            'rubro' => Rubro::class,
            'activa' => 'boolean',
        ];
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'padre_id');
    }

    public function hijas(): HasMany
    {
        return $this->hasMany(Cuenta::class, 'padre_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(DetallePartida::class);
    }

    /** Cuentas de detalle: las que no tienen subcuentas (son las unicas que reciben partidas) */
    public function scopeDeDetalle(Builder $query): void
    {
        $query->whereDoesntHave('hijas');
    }

    public function esDeDetalle(): bool
    {
        return ! $this->hijas()->exists();
    }

    /**
     * Busca la cuenta padre por el codigo: la cuenta existente con el prefijo mas largo.
     */
    public static function buscarPadre(string $codigo): ?self
    {
        $prefijos = [];
        for ($largo = strlen($codigo) - 1; $largo >= 1; $largo--) {
            $prefijos[] = substr($codigo, 0, $largo);
        }

        if (! $prefijos) {
            return null;
        }

        return static::query()->whereIn('codigo', $prefijos)->get()
            ->sortByDesc(fn ($c) => strlen($c->codigo))
            ->first();
    }

    /** Coloca la cuenta en el arbol (padre y nivel) segun su codigo */
    public function ubicarEnArbol(): void
    {
        $padre = static::buscarPadre($this->codigo);
        $this->padre_id = $padre?->id;
        $this->nivel = $padre ? $padre->nivel + 1 : 1;
    }

    /** "110101 - Caja general" */
    public function etiqueta(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }
}